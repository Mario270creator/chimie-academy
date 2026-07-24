# 🌐 Ghid: pune Chimie Academy pe hosting cu cPanel (sau prin Total Commander)

Această versiune a site-ului este scrisă în **PHP + SQLite** și merge pe **orice hosting obișnuit**

## 📁 IMPORTANT · versiunea MySQL cu fișiere plate

Această arhivă e pregătită pentru serverul școlii: **toate fișierele plate** (fără subfoldere)
și **bază de date MySQL** (nu SQLite).

### Pasul 0 — OBLIGATORIU înainte de upload: completează datele MySQL

1. Cere administratorului serverului: numele bazei de date, utilizatorul, parola
   (și host-ul, dacă nu e `localhost`).
2. Deschide fișierul **`core.php`** cu un editor de text (Notepad e suficient).
3. Sus de tot găsești zona marcată `⚙️ CONFIGURARE BAZĂ DE DATE MySQL` — înlocuiește:
   - `COMPLETEAZA_NUMELE_BAZEI` → numele bazei (ex. `cngmm_chimie`)
   - `COMPLETEAZA_UTILIZATORUL` → utilizatorul MySQL
   - `COMPLETEAZA_PAROLA` → parola MySQL
4. Salvează fișierul, apoi urcă totul pe server.

### Instalarea e automată

La prima deschidere a site-ului în browser, aplicația își creează singură toate tabelele
în MySQL și încarcă tot conținutul (conturile demo, clasele, cele 9 lecții și 4 teste,
inclusiv pachetul de Chimie Organică). Nu rulezi niciun script manual.

Dacă datele din core.php sunt greșite, site-ul îți afișează o pagină prietenoasă
care îți spune exact asta (nu o eroare 500 mută).

---

(cPanel, hosting românesc ieftin, hosting gratuit cu PHP). Nu trebuie instalat nimic — doar urci fișierele.

---

## ✅ Ce îți trebuie

1. Un cont de hosting cu **PHP 7.4 sau mai nou** (aproape orice hosting din 2020 încoace).
2. Datele de conectare primite de la hosting: **adresa FTP, utilizator, parolă** — sau acces la **cPanel**.

---

## 📤 Varianta 1: Upload prin cPanel → File Manager (cea mai simplă)

1. Intră în **cPanel** (linkul primit de la firma de hosting, de obicei `numele-site.ro/cpanel`).
2. Deschide **File Manager** (Manager de fișiere).
3. Intră în folderul **`public_html`** (acolo "trăiește" site-ul tău).
4. Apasă **Upload** și urcă arhiva `chimie_academy_php.zip`.
5. După upload, dă **click dreapta pe arhivă → Extract** (dezarhivează direct pe server).
6. Mută conținutul (fișierele .php, folderele `static`, `lab`, `data`) direct în `public_html`
   dacă s-au extras într-un subfolder.
7. Gata! Deschide `numele-site.ro` în browser.

> 💡 Dacă vrei site-ul într-un subfolder (ex. `numele-site.ro/chimie`), extrage totul în
> `public_html/chimie` — funcționează identic, toate linkurile sunt relative.

---

## 📤 Varianta 2: Upload prin Total Commander (FTP)

1. Dezarhivează `chimie_academy_php.zip` pe calculatorul tău.
2. În Total Commander: **Net → FTP Connect (Ctrl+F)** → **New connection**:
   - **Host name:** adresa FTP de la hosting (ex. `ftp.numele-site.ro`)
   - **User name / Password:** cele primite de la hosting
3. Conectează-te. Pe panoul din dreapta vei vedea serverul — intră în **`public_html`**.
4. Pe panoul din stânga navighează la folderul dezarhivat.
5. Selectează **tot conținutul** (Ctrl+A) și apasă **F5 (Copy)** ca să-l urci pe server.
6. Așteaptă să se termine transferul (fișierele mici multe durează câteva minute).
7. Deschide site-ul în browser. Gata!

> ⚠️ În Total Commander lasă modul de transfer pe **Auto/Binary**, nu forța "Text".

---

## 🔑 Conturi la prima pornire

| Cont | Utilizator | Parolă | Rol |
|---|---|---|---|
| Administrator | `profesor_demo` | `1234` | profesor + acces total la panoul ⚙️ Admin |
| Elev demo | `elev_demo` | `1234` | elev |

**⚠️ FOARTE IMPORTANT: schimbă imediat parolele!**
1. Intră cu `profesor_demo` / `1234`.
2. Mergi la **Panou principal → Contul meu → 🔐 Schimbă parola**.
3. Din **⚙️ Admin → Conturi & parole** poți reseta și parola contului `elev_demo`,
   sau poți șterge conturile demo după ce ți-ai făcut conturile reale.

Lecțiile și testele tale existente sunt **deja incluse** în baza de date (`database.sqlite`).

---

## 🗂️ Ce conține site-ul

- `index.php` — pagina de start
- `login.php`, `register.php` — autentificare (înregistrarea publică creează **doar conturi de elev**;
  conturile de profesor se creează din Admin, pentru siguranță)
- `dashboard.php` — panou cu statistici + schimbare parolă
- `lectii.php`, `teste.php`, `test.php` — lecții și teste cu punctaj
- `clase.php`, `clasament.php`, `timer.php` — clase, clasament, cronometru de prezentare
- `admin.php` — panou de administrare (conturi, parole, lecții, teste, clase, anunțuri, activitate)
- `export.php` — backup JSON cu toate datele (buton în Admin → Activitate)
- `laborator.html` — laboratorul interactiv (funcționează și fără cont)
- `database.sqlite` — **baza de date** (aici se salvează tot!)

---

## 🛡️ Protecția bazei de date

Folderul `` conține fișierul `.htaccess` care **blochează accesul din browser** la baza de date.
Verifică după instalare: deschide `numele-site.ro/database.sqlite` — trebuie să primești
**eroare 403 Forbidden**. Dacă în schimb se descarcă fișierul, hostingul tău nu citește .htaccess;
scrie-mi și îți dau soluția alternativă (mutarea bazei deasupra lui public_html).

> 💾 **Backup:** din când în când descarcă fișierul `database.sqlite` prin FTP sau
> folosește butonul **Descarcă backup JSON** din Admin. Acolo sunt toate lecțiile, testele și conturile.

---

## 🔄 Actualizare de la o versiune anterioară (IMPORTANT!)

Dacă **ai deja site-ul instalat** și urci versiunea nouă:

1. Urcă toate fișierele **CU EXCEPȚIA folderului ``** — acolo e baza ta de date cu
   conturile, lecțiile și rezultatele existente. Dacă o suprascrii, pierzi datele!
   (În Total Commander: după Ctrl+A, apasă Insert pe `database.sqlite` ca să-l deselectezi.)
2. După upload, intră cu contul de administrator → **⚙️ Admin → 📦 Pachete de lecții**
   → apasă **„Instalează pachetul acum”** pentru Chimie Organică. Se creează clasa cu
   lecțiile și testele noi, iar toți elevii sunt înscriși automat.

Dacă instalezi **de la zero**, nu trebuie să faci nimic: pachetul de Chimie Organică
este deja inclus în baza de date.

---

## ⚛️ Modulul de Chimie Organică (nou)

Pagina **Organică** din meniu (accesibilă și fără cont) conține:

- **Generator de hidrocarburi** — alegi seria (alcani/alchene/alchine/arene) și numărul de
  atomi de carbon, iar site-ul desenează structura completă (cu toți atomii de H) sau
  scheletul zigzag, plus denumirea, formula moleculară și cea restrânsă. La arene vezi
  benzenul în ambele reprezentări (Kekulé și cercul aromatic).
- **Constructor de catene** — adaugi/scoți atomi de carbon și apeși pe legături ca să le faci
  simple → duble → triple. Numele și formula se actualizează live, iar valența carbonului
  (max. 4 legături) este respectată automat.
- **Arena de antrenament** — întrebări generate la infinit (nume↔formulă, numărul atomilor
  de H, formule generale, recunoașterea structurilor desenate), cu punctaj, serie de
  răspunsuri corecte și record salvat în browser.
- **Fișă rapidă** — tabelul complet al primilor 10 termeni din fiecare serie.

Lecțiile teoretice (5 lecții + 2 teste cu punctaj) sunt în clasa „Chimie Organică · Extra”.

---

## 🔬 Laboratorul — reconstruit complet (v2)

Pagina **Laborator** a fost reconstruită de la zero:

- **Design complet nou** — carcasă „Reactor": topbar de sticlă cu căutare de elemente,
  dock de navigare care urmărește secțiunea activă, hero animat, tot pe estetica site-ului.
- **Ilustrații pentru toate cele 118 elemente** — la click pe un element vezi, pe lângă proprietăți,
  o ilustrație a aspectului lui real: tub cu descărcare colorată pentru gaze (neonul roșu-portocaliu,
  hidrogenul roz), fiolă pentru lichide (brom, mercur), cristale (sulf galben, iod violet), bucăți
  metalice cu luciu (cupru arămiu, aur), capsulă ☢ pentru elementele radioactive.
- **Fotografii reale (opțional)**: pune un fișier `SIMBOL.jpg` (ex. `Fe.jpg`, `Cu.jpg`) în
  `elements/` și site-ul îl folosește automat în locul ilustrației pentru acel element.
- **Căutare de elemente** în bara de sus, cu sugestii (scrie „cupru" sau „Fe" și apasă Enter).
- **Mai optimizat**: pagina a scăzut de la ~140 KB la ~11 KB (motorul chimic e acum fișier separat,
  pe care browserul îl ține în cache), scripturile se încarcă la final, tabelul folosește
  content-visibility.
- **Nimic pierdut**: tabelul cu 118 elemente, reactorul cu egalarea automată a ecuațiilor,
  exemplele rapide, favoritele, istoricul, testul cu 10 reacții, calculatorul de masă molară,
  cele 3 teme (butonul 🌙), experimentele ghidate și jocul de elemente — toate funcționează.
- Vechea pagină rămâne disponibilă la `laborator-clasic.html` (link în subsolul laboratorului).

## 🔬 Laboratorul — modulele adăugate anterior

Laboratorul (pagina **Laborator** din meniu, funcționează fără cont) a primit:

- **Skin futurist** — sticlă, glow-uri și fonturile noi, pe toate cele 3 teme ale lui (light/dark/neon,
  butonul 🌙 din colț). Celulele tabelului periodic „sar" și strălucesc la hover.
- **🧪 Experimente ghidate** — 8 experimente clasice de gimnaziu (neutralizare cu fenolftaleină,
  Zn + HCl, precipitatul albastru, calcar + acid, cuiul de fier în CuSO₄, apa oxigenată cu catalizator,
  testul flăcării, arderea magneziului), fiecare animat pas cu pas într-un pahar virtual: lichide care
  își schimbă culoarea, bule, precipitat care se depune, flacără colorată, fum. Cu ecuația reacției,
  reguli de siguranță și concluzia „Ce am învățat".
- **🔮 Ghicește elementul** — joc cu 25 de elemente și indicii progresive (30/20/10 puncte),
  cu record salvat în browser.
- Tot ce exista rămâne neschimbat: tabelul periodic cu 118 elemente, mixerul de reacții cu egalarea
  automată a ecuațiilor, quiz-ul, calculatorul de masă molară, favoritele și istoricul.

---

## ❓ Probleme frecvente

**Pagina e albă / eroare 500** → hostingul are PHP prea vechi sau lipsește extensia SQLite.
În cPanel caută **Select PHP Version** și alege **PHP 8.1+**, apoi bifează extensiile
**pdo_sqlite** și **sqlite3** dacă nu sunt deja bifate.

**„Sesiunea a expirat" la orice acțiune** → șterge cookie-urile site-ului și reîncearcă.

**Nu pot scrie date (eroare la salvare)** → folderul `` are nevoie de drept de scriere.
În File Manager: click dreapta pe `data` → **Permissions** → setează **755** (sau 775 dacă nu merge).

**Diacriticele arată ciudat** → asigură-te că fișierele au fost transferate Binary, nu Text (vezi nota de la Total Commander).

---

## 🧪 Am modificat ceva și s-a stricat?

Reîncarcă pe server fișierul original din arhivă (suprascrie-l). Baza de date din `` nu se pierde
dacă nu o ștergi tu.

Spor la chimie! ⚗️
