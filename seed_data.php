<?php
/* Conținutul inițial al platformei — încărcat automat la prima rulare pe MySQL.
   Generat din baza SQLite existentă: aceleași conturi, clase, lecții și teste. */
return [
    'users' => [
        ['id' => 1, 'full_name' => 'Profesor Demo', 'username' => 'profesor_demo', 'password_hash' => 'pbkdf2$100000$1dff1804dce89a034477ba90d30bdd26$8a75a1e89fb360312de25150baef1175bef10ba9eb5205a7a9823812fda84c81', 'role' => 'profesor', 'is_admin' => 1, 'bio' => 'Coordonatorul clasei demonstrative și al laboratorului interactiv.', 'created_at' => '2026-04-16T19:45:00+00:00'],
        ['id' => 2, 'full_name' => 'Elev Demo', 'username' => 'elev_demo', 'password_hash' => 'pbkdf2$100000$3871ee5bca6249efbedae2102ab4d624$dd67041e06a16ab8cbbe1e0206a3e94139ef186fa0508f3d6697c14655f5a989', 'role' => 'elev', 'is_admin' => 0, 'bio' => 'Elev demo care își urmărește progresul și acumulează puncte.', 'created_at' => '2026-04-16T19:45:00+00:00'],
    ],
    'classes' => [
        ['id' => 1, 'name' => 'Clasa VII', 'section' => 'A', 'description' => 'Atomii, moleculele, reacțiile de bază și laboratorul vizual.', 'code' => 'CHIM7A', 'teacher_id' => 1, 'created_at' => '2026-04-16T19:45:00+00:00'],
        ['id' => 2, 'name' => 'Clasa VIII', 'section' => 'B', 'description' => 'Acizi, baze, săruri, neutralizare și pregătirea pentru prezentări.', 'code' => 'CHIM8B', 'teacher_id' => 1, 'created_at' => '2026-04-16T19:45:00+00:00'],
        ['id' => 3, 'name' => 'Chimie Organică', 'section' => 'Extra', 'description' => 'Modul suplimentar: hidrocarburi — alcani, alchene, alchine și arene. Include generatorul de structuri și arena de antrenament.', 'code' => 'CHIMORG', 'teacher_id' => 1, 'created_at' => '2026-07-18T07:48:05+00:00'],
    ],
    'enrollments' => [
        ['id' => 1, 'class_id' => 1, 'user_id' => 2, 'joined_at' => '2026-04-16T19:45:00+00:00'],
        ['id' => 2, 'class_id' => 2, 'user_id' => 2, 'joined_at' => '2026-04-16T19:45:00+00:00'],
        ['id' => 3, 'class_id' => 3, 'user_id' => 2, 'joined_at' => '2026-07-18T07:48:05+00:00'],
    ],
    'announcements' => [
        ['id' => 1, 'class_id' => 1, 'teacher_id' => 1, 'title' => 'Bun venit la Chimie Academy', 'content' => 'Primele lecții sunt active. Explorează laboratorul, parcurge lecțiile și rezolvă testele.', 'created_at' => '2026-04-16T19:45:00+00:00'],
        ['id' => 2, 'class_id' => 2, 'teacher_id' => 1, 'title' => 'Laboratorul este gata', 'content' => 'Am inclus simulatorul, exercițiile și timerul de prezentare pentru antrenament rapid.', 'created_at' => '2026-04-16T19:45:00+00:00'],
    ],
    'lessons' => [
        ['id' => 1, 'class_id' => 1, 'title' => 'Lecția 1 · Atomul și identitatea elementelor', 'summary' => 'Noțiunile de bază: protoni, neutroni, electroni și număr atomic.', 'content' => 'Atomul este unitatea de bază a materiei. În această lecție înveți cum diferențiezi protonii, neutronii și electronii, ce înseamnă numărul atomic și cum identifici un element în tabelul periodic.', 'xp' => 40, 'difficulty' => 'Introductiv', 'created_at' => '2026-04-16T19:45:00+00:00'],
        ['id' => 2, 'class_id' => 1, 'title' => 'Lecția 2 · Molecule și formule', 'summary' => 'De la simboluri la compuși: H2O, CO2, NaCl și reguli de citire.', 'content' => 'Formulele chimice arată ce atomi conține un compus și în ce raport. Învață să citești simbolurile și să legi formula de substanța reală.', 'xp' => 55, 'difficulty' => 'Mediu', 'created_at' => '2026-04-16T19:45:00+00:00'],
        ['id' => 3, 'class_id' => 2, 'title' => 'Lecția 3 · Acizi, baze și săruri', 'summary' => 'Recunoaște familiile de compuși și relațiile dintre ele.', 'content' => 'Acizii au, de regulă, hidrogen ionizabil, bazele includ grupa hidroxil, iar sărurile apar frecvent în reacții de neutralizare. Notează exemple, proprietăți și cazuri uzuale.', 'xp' => 65, 'difficulty' => 'Mediu', 'created_at' => '2026-04-16T19:45:00+00:00'],
        ['id' => 4, 'class_id' => 2, 'title' => 'Lecția 4 · Neutralizare și aplicații', 'summary' => 'Cum transformi teoria în experiment și prezentare clară.', 'content' => 'Neutralizarea este una dintre reacțiile-cheie. Urmărește transformarea acid + bază în sare + apă și pregătește modul de explicare orală a rezultatului.', 'xp' => 75, 'difficulty' => 'Avansat', 'created_at' => '2026-04-16T19:45:00+00:00'],
        ['id' => 5, 'class_id' => 3, 'title' => 'Lecția O1 · Ce este chimia organică?', 'summary' => 'Carbonul tetravalent, catenele și clasificarea hidrocarburilor.', 'content' => 'Chimia organică studiază compușii carbonului. Carbonul este special pentru că este TETRAVALENT: fiecare atom de carbon formează exact 4 legături covalente.

Atomii de carbon se pot lega între ei formând CATENE (lanțuri): liniare, ramificate sau ciclice. De aceea există milioane de compuși organici.

HIDROCARBURILE sunt compușii formați DOAR din carbon și hidrogen. Le clasificăm astfel:
• SATURATE — doar legături simple C−C → alcani (ex: metan CH₄, etan C₂H₆)
• NESATURATE — au legături duble C=C → alchene (ex: etenă C₂H₄) sau triple C≡C → alchine (ex: etină C₂H₂)
• AROMATICE (ARENE) — conțin nucleul benzenic (ex: benzen C₆H₆)

Regulă de aur: în orice formulă corectă, fiecare C are 4 legături, iar fiecare H are exact 1 legătură.

💡 Deschide pagina „Organică” din meniu și folosește generatorul ca să vezi structurile desenate.', 'xp' => 45, 'difficulty' => 'Introductiv', 'created_at' => '2026-07-18T07:48:05+00:00'],
        ['id' => 6, 'class_id' => 3, 'title' => 'Lecția O2 · Alcanii — hidrocarburi saturate', 'summary' => 'Seria CₙH₂ₙ₊₂, denumiri de la metan la decan, proprietăți și ardere.', 'content' => 'Alcanii sunt hidrocarburi SATURATE: toate legăturile dintre atomii de carbon sunt simple.

FORMULA GENERALĂ: CₙH₂ₙ₊₂
Verificare: pentru n=1 → CH₄ (metan); n=2 → C₂H₆ (etan); n=3 → C₃H₈ (propan); n=4 → C₄H₁₀ (butan).

DENUMIRI (primii 10 termeni): metan, etan, propan, butan, pentan, hexan, heptan, octan, nonan, decan. Sufixul este mereu „-an”.

STARE DE AGREGARE (la 25°C): C₁–C₄ gaze, C₅–C₁₇ lichide, C₁₈+ solide.

PROPRIETATE CHIMICĂ PRINCIPALĂ — ARDEREA:
CH₄ + 2O₂ → CO₂ + 2H₂O + energie
De aceea metanul (gazul natural) și butanul (brichete, butelii) sunt combustibili.

IZOMERIE: de la butan în sus, aceeași formulă moleculară poate avea structuri diferite. C₄H₁₀ există ca n-butan (catenă liniară) și izobutan (catenă ramificată) — 2 izomeri.

🎯 Exersează în „Organică → Arena de antrenament”, categoria Alcani.', 'xp' => 55, 'difficulty' => 'Mediu', 'created_at' => '2026-07-18T07:48:05+00:00'],
        ['id' => 7, 'class_id' => 3, 'title' => 'Lecția O3 · Alchenele — legătura dublă', 'summary' => 'Seria CₙH₂ₙ, etena, denumirea cu locant și reacția de adiție.', 'content' => 'Alchenele sunt hidrocarburi NESATURATE care conțin o legătură DUBLĂ C=C.

FORMULA GENERALĂ: CₙH₂ₙ (n ≥ 2)
Exemple: C₂H₄ etenă, C₃H₆ propenă, C₄H₈ butenă.

DENUMIRE: sufixul „-enă”. De la 4 atomi de carbon în sus precizăm poziția legăturii duble cu un LOCANT — cel mai mic număr posibil:
CH₂=CH−CH₂−CH₃ → but-1-enă
CH₃−CH=CH−CH₃ → but-2-enă

ETENA (C₂H₄) este cea mai importantă alchenă: din ea se obține POLIETILENA (pungile și foliile de plastic) prin polimerizare. Tot etena grăbește coacerea fructelor.

REACȚIA CARACTERISTICĂ — ADIȚIA: legătura dublă se „deschide” și acceptă atomi noi.
C₂H₄ + H₂ → C₂H₆ (adiția hidrogenului)
C₂H₄ + Br₂ → C₂H₄Br₂ (decolorarea apei de brom = testul pentru nesaturare!)

💡 În constructorul de catene, apasă pe o legătură ca să o transformi din simplă în dublă și urmărește cum se schimbă numele și formula.', 'xp' => 55, 'difficulty' => 'Mediu', 'created_at' => '2026-07-18T07:48:05+00:00'],
        ['id' => 8, 'class_id' => 3, 'title' => 'Lecția O4 · Alchinele — legătura triplă', 'summary' => 'Seria CₙH₂ₙ₋₂, acetilena și flacăra de sudură.', 'content' => 'Alchinele sunt hidrocarburi NESATURATE care conțin o legătură TRIPLĂ C≡C.

FORMULA GENERALĂ: CₙH₂ₙ₋₂ (n ≥ 2)
Exemple: C₂H₂ etină, C₃H₄ propină, C₄H₆ butină.

DENUMIRE: sufixul „-ină”, cu locant de la 4 atomi de carbon: but-1-ină, but-2-ină.

ETINA (C₂H₂), numită popular ACETILENĂ, are structura H−C≡C−H. Arde cu o flacără foarte fierbinte (~3000°C în oxigen), de aceea se folosește la SUDURA ȘI TĂIEREA metalelor (flacăra oxiacetilenică):
2C₂H₂ + 5O₂ → 4CO₂ + 2H₂O + energie

Ca și alchenele, alchinele dau reacții de ADIȚIE și decolorează apa de brom — semn de nesaturare.

ATENȚIE la comparație: pentru același n, alcanul are cei mai mulți H, alchena cu 2 mai puțini, alchina cu 4 mai puțini. Exemplu n=2: C₂H₆ / C₂H₄ / C₂H₂.', 'xp' => 55, 'difficulty' => 'Mediu', 'created_at' => '2026-07-18T07:48:05+00:00'],
        ['id' => 9, 'class_id' => 3, 'title' => 'Lecția O5 · Arenele — benzenul și nucleul aromatic', 'summary' => 'C₆H₆, hexagonul aromatic, toluenul și unde întâlnim arenele.', 'content' => 'ARENELE (hidrocarburile aromatice) conțin NUCLEUL BENZENIC: un ciclu de 6 atomi de carbon.

BENZENUL, C₆H₆, este cea mai simplă arenă. Kekulé l-a desenat ca un hexagon cu legături duble și simple alternante. În realitate, cei 6 electroni sunt DELOCALIZAȚI pe tot ciclul — de aceea benzenul se desenează adesea ca un hexagon cu un CERC în interior. Toate legăturile C−C din benzen sunt identice!

FORMULA GENERALĂ a seriei: CₙH₂ₙ₋₆ (n ≥ 6).
Următorul termen: METILBENZENUL (toluenul), C₇H₈ — un nucleu benzenic cu o grupare −CH₃.

Deși pare foarte nesaturat, benzenul NU decolorează ușor apa de brom — nucleul aromatic este neobișnuit de stabil. Reacțiile lui caracteristice sunt de SUBSTITUȚIE, nu de adiție.

UNDE ÎNTÂLNIM ARENE: în benzină, în materiale plastice (polistiren), coloranți, medicamente (aspirina se obține pornind de la arene).

⚠️ Benzenul este TOXIC și cancerigen — în laboratorul școlar nu se lucrează cu el; îl studiem doar teoretic.

🔬 Deschide „Organică → Generator” și alege „Arene” ca să vezi hexagonul benzenic desenat în ambele reprezentări.', 'xp' => 60, 'difficulty' => 'Avansat', 'created_at' => '2026-07-18T07:48:05+00:00'],
    ],
    'completions' => [
    ],
    'quizzes' => [
        ['id' => 1, 'class_id' => 1, 'title' => 'Test 1 · Tabel periodic', 'description' => 'Trei întrebări rapide pentru verificarea noțiunilor de bază.', 'xp' => 120, 'difficulty' => 'Standard', 'questions_json' => '[{"text": "Care este simbolul oxigenului?", "options": ["O", "Ox", "Og", "Om"], "correct": 0, "explanation": "Simbolul chimic al oxigenului este O."}, {"text": "Numărul atomic este egal cu numărul de...", "options": ["neutroni", "protoni", "molecule", "orbitali"], "correct": 1, "explanation": "Numărul atomic reprezintă numărul de protoni din nucleu."}, {"text": "Gazele nobile se află în grupa...", "options": ["1", "2", "17", "18"], "correct": 3, "explanation": "Gazele nobile se află în grupa 18."}]', 'created_at' => '2026-04-16T19:45:00+00:00'],
        ['id' => 2, 'class_id' => 2, 'title' => 'Test 2 · Acizi și baze', 'description' => 'Testează reacțiile esențiale și modul de explicare a rezultatului.', 'xp' => 150, 'difficulty' => 'Standard', 'questions_json' => '[{"text": "Ce se obține, de regulă, în reacția acid + bază?", "options": ["metal și oxid", "gaz inert", "sare și apă", "numai apă"], "correct": 2, "explanation": "Neutralizarea produce de obicei sare și apă."}, {"text": "Care dintre următoarele este o bază?", "options": ["NaOH", "HCl", "CO2", "SO2"], "correct": 0, "explanation": "NaOH este hidroxid de sodiu, deci o bază."}, {"text": "Ce suport te ajută să explici clar un proiect experimental?", "options": ["Poster și/sau exponat", "Doar eseu", "Doar video", "Doar model 3D online"], "correct": 0, "explanation": "Un poster și/sau un exponat pot susține clar explicația unui proiect experimental."}]', 'created_at' => '2026-04-16T19:45:00+00:00'],
        ['id' => 3, 'class_id' => 3, 'title' => 'Test O1 · Alcanii', 'description' => 'Formule moleculare, denumiri și proprietățile hidrocarburilor saturate.', 'xp' => 140, 'difficulty' => 'Standard', 'questions_json' => '[{"text": "Care este formula generală a alcanilor?", "options": ["CₙH₂ₙ₊₂", "CₙH₂ₙ", "CₙH₂ₙ₋₂", "CₙH₂ₙ₋₆"], "correct": 0, "explanation": "Alcanii sunt saturați: CₙH₂ₙ₊₂. Pentru n=1 obținem CH₄."}, {"text": "Ce alcan are formula C₅H₁₂?", "options": ["Butan", "Pentan", "Hexan", "Propan"], "correct": 1, "explanation": "Prefixul „pent-” înseamnă 5 atomi de carbon: pentan, C₅H₁₂."}, {"text": "Câți atomi de hidrogen are octanul?", "options": ["16", "20", "18", "17"], "correct": 2, "explanation": "Octan: n=8, deci H = 2·8+2 = 18. Formula: C₈H₁₈."}, {"text": "Care este produsul arderii complete a metanului?", "options": ["CO₂ și H₂O", "CO și H₂", "C și H₂O", "doar CO₂"], "correct": 0, "explanation": "CH₄ + 2O₂ → CO₂ + 2H₂O + energie."}, {"text": "În condiții normale (25°C), butanul este:", "options": ["lichid", "solid", "gaz", "plasmă"], "correct": 2, "explanation": "Termenii C₁–C₄ ai seriei alcanilor sunt gaze — de aceea butanul stă comprimat în brichete."}, {"text": "Câți izomeri are C₄H₁₀?", "options": ["1", "2", "3", "4"], "correct": 1, "explanation": "n-butan (catenă liniară) și izobutan (catenă ramificată)."}]', 'created_at' => '2026-07-18T07:48:05+00:00'],
        ['id' => 4, 'class_id' => 3, 'title' => 'Test O2 · Nesaturate și arene', 'description' => 'Alchene, alchine, benzen: formule generale, denumiri cu locant și reacții.', 'xp' => 150, 'difficulty' => 'Avansat', 'questions_json' => '[{"text": "Care serie are formula generală CₙH₂ₙ?", "options": ["Alcanii", "Alchenele", "Alchinele", "Arenele"], "correct": 1, "explanation": "Legătura dublă „consumă” 2 atomi de H față de alcan: CₙH₂ₙ."}, {"text": "CH₃−CH=CH−CH₃ se numește:", "options": ["but-1-enă", "but-2-enă", "butan", "but-2-ină"], "correct": 1, "explanation": "Legătura dublă începe la carbonul 2 → but-2-enă."}, {"text": "Ce hidrocarbură are formula C₃H₄?", "options": ["Propan", "Propenă", "Propină", "Benzen"], "correct": 2, "explanation": "2·3−2 = 4 atomi de H → seria alchinelor: propină."}, {"text": "Acetilena (etina) se folosește la:", "options": ["coacerea fructelor", "sudura metalelor", "pungile de plastic", "parfumuri"], "correct": 1, "explanation": "Flacăra oxiacetilenică atinge ~3000°C — ideală pentru sudură și tăiere."}, {"text": "Formula moleculară a benzenului este:", "options": ["C₆H₁₂", "C₆H₆", "C₆H₁₄", "C₆H₈"], "correct": 1, "explanation": "Benzenul: nucleu de 6 atomi de C cu 6 atomi de H — C₆H₆, seria CₙH₂ₙ₋₆."}, {"text": "Testul cu apă de brom deosebește:", "options": ["alcanii de gaze", "hidrocarburile nesaturate de cele saturate", "lichidele de solide", "metalele de nemetale"], "correct": 1, "explanation": "Alchenele și alchinele decolorează apa de brom (adiție); alcanii nu."}]', 'created_at' => '2026-07-18T07:48:05+00:00'],
    ],
    'attempts' => [
    ],
];
