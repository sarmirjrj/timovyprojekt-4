# STUDDIT – Študentský portál pre zdieľanie materiálov

## O projekte  
STUDDIT je webová platforma určená pre študentov UCM FPV (fakulta UPTI), kde môžu zdieľať študijné materiály, vytvárať skupiny podľa predmetov alebo ročníkov a komunikovať medzi sebou. Projekt má za cieľ uľahčiť štúdium, podporiť spoluprácu a zdieľanie poznatkov.

## Funkcie  
- Registrácia a prihlásenie používateľov  
- Vytváranie skupín (predmetových, ročníkových)  
- Zdieľanie a nahrávanie súborov (materiály)  
- Komentovanie príspevkov a diskusné vlákna  
- Zobrazenie feedu príspevkov a filtrovanie podľa skupiny/predmetu

## Použité technológie  
- **Frontend:** HTML, CSS 
- **Backend:** PHP  
- **Databáza:** MySQL  
- **Verziovanie a spolupráca:** Git, GitHub  
- **Hosting / server:** univerzitný server, možná Docker kontajnerizácia  

## Rýchle spustenie pomocou Dockeru  
Ak máte nainštalovaný Docker a Docker Compose, môžete projekt spustiť lokálne veľmi jednoducho:

1. Skopírujte si repozitár:  
   ```bash
   git clone https://github.com/sarmirjrj/timovyprojekt-4.git
   cd timovyprojekt-4
2. Skontrolujte súbor docker-compose.yaml
3. Spustite projekt `docker-compose up -d`
4. V prehliadači prejdite na http://localhost:8080 pre DB a http://localhost:80 pre backend
5. Pre ukončenie kontajnerov `docker-compose down`

## Štruktúra repozitára
timovyprojekt-4/
│
├── Backend/        ← PHP API a spracovanie požiadaviek + Frontend projektu (HTML/CSS)
├── WEB/            ← Projektová stránka (HTML/CSS)  
├── database/       ← SQL skripty, databázová schéma  
├── docker-compose.yaml  
├── .gitignore  
└── README.md

## Tím projektu

Juraj Šarmír – Projektový manažér
Matej – Backend (PHP API, logika)
Valentin – Frontend (HTML, CSS, API integrácia)
Alex – Databázy (MySQL, návrh a optimalizácia)
Matúš – Testing (testovacie scenáre, QA)
Daniel – Dizajn & Content (UI návrh, dokumentačný web)

