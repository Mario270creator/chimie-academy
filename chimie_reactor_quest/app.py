from __future__ import annotations

import csv
import io
import json
import os
import random
import secrets
import sqlite3
import string
import sys
from contextlib import closing
from datetime import date, datetime, timedelta, timezone
from functools import wraps
from pathlib import Path
from typing import Any

BOOT_DIR = Path(__file__).resolve().parent
VENDOR_DIR = BOOT_DIR / "_vendor"


def ensure_local_vendor_packages() -> None:
    if VENDOR_DIR.exists() and str(VENDOR_DIR) not in sys.path:
        sys.path.insert(0, str(VENDOR_DIR))


try:
    import flask  # noqa: F401
    import werkzeug  # noqa: F401
except Exception:
    ensure_local_vendor_packages()

from flask import (
    Flask,
    Response,
    flash,
    g,
    jsonify,
    redirect,
    render_template,
    request,
    send_from_directory,
    session,
    url_for,
)
from werkzeug.security import check_password_hash, generate_password_hash

BASE_DIR = Path(__file__).resolve().parent
INSTANCE_DIR = BASE_DIR / "instance"
DEFAULT_RENDER_DISK_DIR = Path("/opt/render/project/src/render_disk")
PERSISTENT_DATA_DIR = DEFAULT_RENDER_DISK_DIR if os.environ.get("RENDER") and DEFAULT_RENDER_DISK_DIR.exists() else INSTANCE_DIR
DB_PATH = Path(os.environ.get("APP_DB_PATH", PERSISTENT_DATA_DIR / "chimie_reactor_quest.db"))
SECRET_FILE = Path(os.environ.get("APP_SECRET_FILE", PERSISTENT_DATA_DIR / ".secret_key"))
LEGACY_IMPORT_FILE = Path(os.environ.get("APP_LEGACY_IMPORT_FILE", PERSISTENT_DATA_DIR / "legacy_bridge_last_import.json"))

INSTANCE_DIR.mkdir(parents=True, exist_ok=True)
DB_PATH.parent.mkdir(parents=True, exist_ok=True)
SECRET_FILE.parent.mkdir(parents=True, exist_ok=True)
LEGACY_IMPORT_FILE.parent.mkdir(parents=True, exist_ok=True)


def load_secret_key() -> str:
    env_secret = os.environ.get("SECRET_KEY")
    if env_secret:
        return env_secret
    if SECRET_FILE.exists():
        return SECRET_FILE.read_text(encoding="utf-8").strip()
    secret = secrets.token_urlsafe(32)
    SECRET_FILE.write_text(secret, encoding="utf-8")
    return secret


app = Flask(__name__)
app.config.update(
    SECRET_KEY=load_secret_key(),
    JSON_AS_ASCII=False,
    TEMPLATES_AUTO_RELOAD=True,
)


# ---------------------------- database helpers ---------------------------- #

SCHEMA_SQL = """
PRAGMA foreign_keys = ON;

CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    full_name TEXT NOT NULL,
    username TEXT NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    role TEXT NOT NULL CHECK(role IN ('profesor', 'elev', 'admin')),
    bio TEXT DEFAULT '',
    created_at TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS classes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    section TEXT NOT NULL,
    description TEXT NOT NULL,
    code TEXT NOT NULL UNIQUE,
    teacher_id INTEGER NOT NULL,
    created_at TEXT NOT NULL,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS enrollments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    class_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    joined_at TEXT NOT NULL,
    UNIQUE(class_id, user_id),
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS announcements (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    class_id INTEGER NOT NULL,
    teacher_id INTEGER NOT NULL,
    title TEXT NOT NULL,
    content TEXT NOT NULL,
    created_at TEXT NOT NULL,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS lessons (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    class_id INTEGER NOT NULL,
    title TEXT NOT NULL,
    summary TEXT NOT NULL,
    content TEXT NOT NULL,
    xp INTEGER NOT NULL DEFAULT 40,
    difficulty TEXT NOT NULL DEFAULT 'Mediu',
    created_at TEXT NOT NULL,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS completions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    lesson_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    completed_at TEXT NOT NULL,
    UNIQUE(lesson_id, user_id),
    FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS quizzes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    class_id INTEGER NOT NULL,
    title TEXT NOT NULL,
    description TEXT NOT NULL,
    xp INTEGER NOT NULL DEFAULT 120,
    difficulty TEXT NOT NULL DEFAULT 'Standard',
    questions_json TEXT NOT NULL,
    created_at TEXT NOT NULL,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS attempts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    quiz_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    score_percent REAL NOT NULL,
    correct_count INTEGER NOT NULL,
    total_count INTEGER NOT NULL,
    answers_json TEXT NOT NULL,
    created_at TEXT NOT NULL,
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS projects (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    owner_id INTEGER NOT NULL,
    class_id INTEGER,
    title TEXT NOT NULL,
    acronym TEXT NOT NULL,
    team_category TEXT NOT NULL CHECK(team_category IN ('Juniori', 'Seniori')),
    section TEXT NOT NULL CHECK(section IN ('A', 'B', 'C')),
    mentor_name TEXT NOT NULL,
    school_name TEXT NOT NULL,
    member_one TEXT NOT NULL,
    member_one_role TEXT NOT NULL,
    member_two TEXT NOT NULL,
    member_two_role TEXT NOT NULL,
    problem TEXT NOT NULL,
    objectives TEXT NOT NULL,
    methods TEXT NOT NULL,
    novelty TEXT NOT NULL,
    results TEXT NOT NULL,
    next_steps TEXT NOT NULL,
    started_on TEXT NOT NULL,
    updated_at TEXT NOT NULL,
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE SET NULL
);
"""


def utcnow_iso() -> str:
    return datetime.now(timezone.utc).replace(microsecond=0).isoformat()


def get_db() -> sqlite3.Connection:
    if "db" not in g:
        conn = sqlite3.connect(DB_PATH)
        conn.row_factory = sqlite3.Row
        conn.execute("PRAGMA foreign_keys = ON")
        g.db = conn
    return g.db


@app.teardown_appcontext
def close_db(exc: Exception | None) -> None:
    db = g.pop("db", None)
    if db is not None:
        db.close()


def execute_db(sql: str, params: tuple[Any, ...] = ()) -> sqlite3.Cursor:
    db = get_db()
    cur = db.execute(sql, params)
    db.commit()
    return cur


def query_all(sql: str, params: tuple[Any, ...] = ()) -> list[sqlite3.Row]:
    return get_db().execute(sql, params).fetchall()


def query_one(sql: str, params: tuple[Any, ...] = ()) -> sqlite3.Row | None:
    return get_db().execute(sql, params).fetchone()


def init_db() -> None:
    with closing(sqlite3.connect(DB_PATH)) as conn:
        conn.executescript(SCHEMA_SQL)
        conn.commit()


def make_class_code(name: str, section: str) -> str:
    base = "".join(ch for ch in (name + section).upper() if ch.isalnum())[:6] or "CLASA"
    while True:
        suffix = "".join(random.choice(string.digits) for _ in range(3))
        code = f"{base}{suffix}"
        if query_one("SELECT id FROM classes WHERE code = ?", (code,)) is None:
            return code


def seed_demo_data() -> None:
    if query_one("SELECT id FROM users LIMIT 1"):
        return

    now = utcnow_iso()
    teacher_password = generate_password_hash("1234")
    student_password = generate_password_hash("1234")

    teacher_id = execute_db(
        "INSERT INTO users(full_name, username, password_hash, role, bio, created_at) VALUES (?, ?, ?, ?, ?, ?)",
        (
            "Profesor Demo",
            "profesor_demo",
            teacher_password,
            "profesor",
            "Coordonatorul clasei demonstrative și al laboratorului interactiv.",
            now,
        ),
    ).lastrowid

    student_id = execute_db(
        "INSERT INTO users(full_name, username, password_hash, role, bio, created_at) VALUES (?, ?, ?, ?, ?, ?)",
        (
            "Elev Demo",
            "elev_demo",
            student_password,
            "elev",
            "Elev demo care își urmărește progresul și acumulează puncte.",
            now,
        ),
    ).lastrowid

    class_one = execute_db(
        "INSERT INTO classes(name, section, description, code, teacher_id, created_at) VALUES (?, ?, ?, ?, ?, ?)",
        (
            "Clasa VII",
            "A",
            "Atomii, moleculele, reacțiile de bază și laboratorul vizual.",
            "CHIM7A",
            teacher_id,
            now,
        ),
    ).lastrowid

    class_two = execute_db(
        "INSERT INTO classes(name, section, description, code, teacher_id, created_at) VALUES (?, ?, ?, ?, ?, ?)",
        (
            "Clasa VIII",
            "B",
            "Acizi, baze, săruri, neutralizare și pregătirea pentru prezentări.",
            "CHIM8B",
            teacher_id,
            now,
        ),
    ).lastrowid

    execute_db(
        "INSERT INTO enrollments(class_id, user_id, joined_at) VALUES (?, ?, ?)",
        (class_one, student_id, now),
    )
    execute_db(
        "INSERT INTO enrollments(class_id, user_id, joined_at) VALUES (?, ?, ?)",
        (class_two, student_id, now),
    )

    execute_db(
        "INSERT INTO announcements(class_id, teacher_id, title, content, created_at) VALUES (?, ?, ?, ?, ?)",
        (
            class_one,
            teacher_id,
            "Bun venit la Chimie Academy",
            "Primele lecții sunt active. Explorează laboratorul, parcurge lecțiile și rezolvă testele.",
            now,
        ),
    )
    execute_db(
        "INSERT INTO announcements(class_id, teacher_id, title, content, created_at) VALUES (?, ?, ?, ?, ?)",
        (
            class_two,
            teacher_id,
            "Laboratorul este gata",
            "Am inclus simulatorul, exercițiile și timerul de prezentare pentru antrenament rapid.",
            now,
        ),
    )

    lessons = [
        (
            class_one,
            "Lecția 1 · Atomul și identitatea elementelor",
            "Noțiunile de bază: protoni, neutroni, electroni și număr atomic.",
            "Atomul este unitatea de bază a materiei. În această lecție înveți cum diferențiezi protonii, neutronii și electronii, ce înseamnă numărul atomic și cum identifici un element în tabelul periodic.",
            40,
            "Introductiv",
        ),
        (
            class_one,
            "Lecția 2 · Molecule și formule",
            "De la simboluri la compuși: H2O, CO2, NaCl și reguli de citire.",
            "Formulele chimice arată ce atomi conține un compus și în ce raport. Învață să citești simbolurile și să legi formula de substanța reală.",
            55,
            "Mediu",
        ),
        (
            class_two,
            "Lecția 3 · Acizi, baze și săruri",
            "Rec
