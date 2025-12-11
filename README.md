# DevOps TODO API - Plain PHP + MySQL

> Felhő és DevOps alapok kurzus projektmunka - Egyszerű PHP backend, MySQL adatbázis, teljes DevOps pipeline

## Projekt Áttekintés

Ez egy **sima PHP-ban írt REST API TODO alkalmazás**, amely teljes körű DevOps környezettel van ellátva. Az alkalmazás MySQL adatbázist használ, PHPMyAdmin felülettel, és modern DevOps gyakorlatokat implementál - **Laravel nélkül, csak tiszta PHP kóddal**.

### Miért Plain PHP?

- Egyszerű telepítés - nincs szükség Composer-re vagy összetett függőségekre
- Könnyű megértés - átlátható kódstruktúra
- Gyors futtatás - minimális overhead
- Tanulási célokra ideális - tisztán látszanak a DevOps folyamatok

### Technológiai Stack

**Backend:**
- PHP 8.2+
- Plain PHP (MVC pattern)
- MySQL 8.0
- Apache Web Server
- PDO (PHP Data Objects)

**Monitoring:**
- Prometheus (metrika gyűjtés)
- Grafana (vizualizáció)

**DevOps Eszközök:**
1. **Git** - Verziókezelés
2. **GitHub Actions** - CI/CD pipeline
3. **Docker** - Konténerizáció
4. **Docker Compose** - Lokális orchestration
5. **Kubernetes (Minikube)** - Container orchestration
6. **Prometheus** - Monitoring
7. **Grafana** - Visualization
8. **PHPMyAdmin** - Database management

## Funkcionalitás

### API Végpontok

- `GET /api` - API információk
- `GET /api/health` - Health check (database status, uptime)
- `GET /api/metrics` - Prometheus metrikák
- `GET /api/todos` - Összes TODO listázása (filter: `completed`, `priority`)
- `GET /api/todos/{id}` - Egy TODO lekérése
- `POST /api/todos` - Új TODO létrehozása
- `PUT /api/todos/{id}` - TODO módosítása
- `PATCH /api/todos/{id}/toggle` - TODO befejezettségének váltása
- `DELETE /api/todos/{id}` - TODO törlése

### TODO Adatstruktúra

```json
{
  "id": 1,
  "title": "Feladat címe",
  "description": "Részletes leírás",
  "completed": false,
  "priority": "high",
  "due_date": "2025-12-31 23:59:59",
  "created_at": "2025-11-27 10:00:00",
  "updated_at": "2025-11-27 10:00:00"
}
```

## Telepítés és Futtatás

### Előfeltételek

- Docker Desktop telepítve és futva
- Git
- (Opcionális) PHP 8.2+ lokális teszteléshez
- (Opcionális) Minikube Kubernetes deployment-hez

### 1. Gyors Indítás Docker Compose-zal

**Legegyszerűbb módszer - egyetlen parancs:**

```bash
# Repository klónozása
git clone https://github.com/TatLiliana/devops-todo-project.git
cd devops-todo-project

# Alkalmazás indítása (adatbázis létrehozással együtt)
docker-compose up -d
```

**Elérhetőségek:**
- PHP TODO API: http://localhost:8000/api
- PHPMyAdmin: http://localhost:8080 (root/rootpass)
- Prometheus: http://localhost:9090
- Grafana: http://localhost:4000 (admin/admin)
- MySQL: localhost:3306

**Leállítás:**
```bash
docker-compose down

# Vagy adatokkal együtt:
docker-compose down -v
```

### 2. Lokális Fejlesztés (PHP beépített szerver)

```bash
# PHP beépített szerverrel
php -S localhost:8000 -t public

# Vagy Apache-al
# Másold a projektet a htdocs mappába és nyisd meg a böngészőben
```

### 3. Kubernetes Deployment (Minikube)

```bash
# Minikube indítása
minikube start --cpus=4 --memory=4096

# Docker image build a Minikube környezetben
eval $(minikube docker-env)
docker build -t php-todo-api:latest .

# Kubernetes resources telepítése
kubectl apply -f k8s/namespace.yml
kubectl apply -f k8s/mysql.yml
kubectl apply -f k8s/app-deployment.yml
kubectl apply -f k8s/app-service.yml

# Service URL lekérése
minikube service php-todo-api-service -n php-todo-app --url

# Állapot ellenőrzés
kubectl get pods -n php-todo-app
kubectl get services -n php-todo-app

# Logok megtekintése
kubectl logs -n php-todo-app -l app=php-todo-api
```

## CI/CD Pipeline

### CI Pipeline (Continuous Integration)

**Trigger:** Push a `main` vagy `develop` branch-re, vagy Pull Request

**Multi-Version Testing:**
A CI pipeline **párhuzamosan fut PHP 8.2 és PHP 8.3 verziókon** is, biztosítva a kompatibilitást.

**Lépések (minden PHP verziónál):**
1. Kód checkout
2. **PHP 8.2 job** (párhuzamos)
   - MySQL service indítása
   - PHP beépített szerver indítása
   - API endpoint tesztek:
     - ✅ Health check (`/api/health`)
     - ✅ Metrics endpoint (`/api/metrics`)
     - ✅ Todos API (`/api/todos`)
3. **PHP 8.3 job** (párhuzamos)
   - Ugyanazok a tesztek
4. **Docker Build & Test** (ha mindkét PHP teszt sikeres)
   - Docker image build (Buildx)
   - Image load local registry-be
   - Container indítási teszt
   - Container működés validálás

**GitHub Actions Matrix Strategy:**
```yaml
strategy:
  matrix:
    php-version: ['8.2', '8.3']
```

**Fájl:** `.github/workflows/ci.yml`

**Megtekintés:** https://github.com/TatLiliana/devops-todo-project/actions

### CD Pipeline (Continuous Deployment)

**Trigger:** Push a `main` branch-re

**Lépések:**
1. Docker image build
2. Docker Hub-ra push (latest + SHA tag)
3. Automatikus verziókezelés

**Fájl:** `.github/workflows/cd.yml`

### GitHub Secrets Beállítása

A CD pipeline működéséhez szükséges secrets:

```
DOCKER_USERNAME      # Docker Hub felhasználónév
DOCKER_PASSWORD      # Docker Hub token/jelszó
```

Beállítás: GitHub repository → Settings → Secrets and variables → Actions → New repository secret

## Branch Struktúra és Workflow

### Elérhető Branch-ek

**`main`** - Production branch
- Minden push triggerel CI és CD pipeline-t
- Automatikus deploy Docker Hub-ra
- Production-ready kód
- **Védett branch** (ajánlott)

**`develop`** - Development/Staging branch
- Minden push triggerel CI pipeline-t (tesztelés)
- CD pipeline NEM fut (nincs production deploy)
- Fejlesztés alatt álló funkciók
- Pull Request-ek innen mennek `main`-re

### Fejlesztési Workflow

```bash
# 1. Új funkció fejlesztése
git checkout develop
git pull origin develop

# 2. Változtatások
# ... kód írása ...
git add .
git commit -m "Add new feature"
git push origin develop
# → CI pipeline fut (PHP 8.2 + 8.3 tesztek, Docker build)

# 3. Amikor production-ready
git checkout main
git merge develop
git push origin main
# → CI + CD pipeline fut (tesztek + Docker Hub deploy)
```

### Pull Request Workflow (Ajánlott)

```bash
# 1. Feature branch
git checkout -b feature/my-feature
# ... kód írása ...
git push origin feature/my-feature

# 2. GitHub-on: Create Pull Request → develop vagy main
# → CI pipeline fut automatikusan

# 3. Code review után merge
# → Ha main-re merge-eltél: CI + CD fut
```

### CI/CD Pipeline Triggerek

| Branch/Esemény | CI (Build & Test) | CD (Deploy) |
|---------------|-------------------|-------------|
| Push to `main` | ✅ **FUT** | ✅ **FUT** |
| Push to `develop` | ✅ **FUT** | ❌ NEM FUT |
| Pull Request → `main` | ✅ **FUT** | ❌ NEM FUT |
| Pull Request → `develop` | ✅ **FUT** | ❌ NEM FUT |

**Részletek:** Lásd `PIPELINE-TRIGGERS.md`

## Monitoring és Metrikák

### Prometheus Metrikák

Az alkalmazás a `/api/metrics` endpoint-on exportálja a következő metrikákat:

**TODO metrikák:**
- `total_todos` - Összes TODO
- `active_todos` - Aktív (nem befejezett) TODO-k száma
- `completed_todos` - Befejezett TODO-k száma
- `todos_by_priority{priority="high|medium|low"}` - TODO-k prioritás szerint

**PHP metrikák:**
- `php_memory_usage_bytes` - Aktuális PHP memória használat
- `php_memory_peak_bytes` - Peak PHP memória használat

**HTTP metrikák:**
- `http_requests_total` - HTTP kérések száma

### Prometheus Használata

1. Nyisd meg: http://localhost:9090
2. Példa query-k:

```promql
# Aktív TODO-k
active_todos

# Magas prioritású TODO-k
todos_by_priority{priority="high"}

# Összes TODO időbeli változása
rate(total_todos[5m])
```

### Grafana Dashboard

1. Nyisd meg: http://localhost:4000
2. Belépés: `admin` / `admin`
3. Prometheus data source már be van konfigurálva
4. Dashboard készítése:
   - Add panel
   - Válassz metrikát (pl. `active_todos`)
   - Customize visualization

## API Használat - Példák

### 1. API Információk

```bash
curl http://localhost:8000/api
```

### 2. Health Check

```bash
curl http://localhost:8000/api/health
```

### 3. TODO Létrehozása

```bash
curl -X POST http://localhost:8000/api/todos \
  -H "Content-Type: application/json" \
  -d '{
    "title": "DevOps projektmunka befejezése",
    "description": "README, dokumentáció és védés előkészítése",
    "priority": "high",
    "due_date": "2025-12-01 23:59:59"
  }'
```

### 4. Összes TODO Listázása

```bash
# Összes
curl http://localhost:8000/api/todos

# Csak befejezettek
curl http://localhost:8000/api/todos?completed=true

# Magas prioritásúak
curl http://localhost:8000/api/todos?priority=high
```

### 5. TODO Módosítása

```bash
curl -X PUT http://localhost:8000/api/todos/1 \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Módosított cím",
    "completed": true
  }'
```

### 6. TODO Befejezettség Váltása

```bash
curl -X PATCH http://localhost:8000/api/todos/1/toggle
```

### 7. TODO Törlése

```bash
curl -X DELETE http://localhost:8000/api/todos/1
```

## Projekt Struktúra

```
megoldas_mvc-php/
├── public/
│   ├── index.php              # Fő belépési pont, routing
│   └── .htaccess              # Apache rewrite rules
├── src/
│   ├── Database.php           # Adatbázis kapcsolat és schema
│   ├── TodoController.php     # TODO CRUD műveletek
│   ├── HealthController.php   # Health check endpoint
│   └── MetricsController.php  # Prometheus metrikák
├── database/
│   └── schema.sql             # MySQL schema és példa adatok
├── k8s/
│   ├── namespace.yml          # Kubernetes namespace
│   ├── mysql.yml              # MySQL StatefulSet
│   ├── app-deployment.yml     # App Deployment
│   └── app-service.yml        # App Service (NodePort)
├── .github/
│   └── workflows/
│       ├── ci.yml             # CI pipeline
│       └── cd.yml             # CD pipeline
├── prometheus/
│   └── prometheus.yml         # Prometheus konfiguráció
├── grafana/
│   └── provisioning/
│       └── datasources/
│           └── prometheus.yml # Grafana datasource
├── Dockerfile                 # Docker image build
├── docker-compose.yml         # Docker Compose setup
├── .env.example               # Environment változók példa
├── .htaccess                  # Root Apache config
├── .gitignore                 # Git ignore rules
└── README.md                  # Ez a fájl
```

## Hibaelhárítás

### MySQL kapcsolódási hiba

```bash
# MySQL container ellenőrzése
docker ps | grep mysql

# MySQL logok
docker logs todo-mysql

# Manuális kapcsolódás tesztelése
docker exec -it todo-mysql mysql -u todouser -ptodopass todoapp
```

### Apache permission error

```bash
# Jogosultságok beállítása
docker exec todo-app chmod -R 755 /var/www/html
docker exec todo-app chown -R www-data:www-data /var/www/html
```

### Port foglalt

```bash
# Futó folyamatok ellenőrzése
netstat -ano | findstr :8000

# Vagy használj másik portot
# docker-compose.yml-ben módosítsd: "8001:80"
```

### Docker image build hiba

```bash
# Cache nélküli build
docker-compose build --no-cache

# Részletes build log
docker-compose build --progress=plain
```

## PHPMyAdmin Használat

1. Megnyitás: http://localhost:8080
2. Szerver: `mysql`
3. Felhasználó: `root`
4. Jelszó: `rootpass`
5. Adatbázis: `todoapp`

**Funkciók:**
- `todos` tábla böngészése
- SQL query futtatás
- Adatok kézi módosítása
- Export/Import

## DevOps Követelmények Teljesítése

### Code (10 pont)
- ✅ Tiszta, olvasható PHP kód
- ✅ MVC pattern alkalmazása
- ✅ REST API best practices
- ✅ Adatbázis indexek használata
- ✅ Környezeti változók kezelése

### Build & Test (15 pont)
- ✅ Automatizált CI pipeline
- ✅ Multi-version PHP tesztelés (8.2, 8.3)
- ✅ Docker image build
- ✅ API endpoint tesztelés
- ✅ Health check implementálás

### Release & Deploy (15 pont)
- ✅ Docker konténerizáció
- ✅ Docker Compose orchestration
- ✅ Kubernetes deployment
- ✅ Automatizált CD pipeline
- ✅ Docker Hub publikálás

### Monitor & Feedback (10 pont)
- ✅ Prometheus metrika gyűjtés
- ✅ Grafana vizualizáció
- ✅ Health check endpoint
- ✅ Application metrics
- ✅ Database monitoring

### Tool-ok: 5+ használata (Git, Docker nem számít)
1. ✅ **GitHub Actions** - CI/CD automation
2. ✅ **Docker Compose** - Multi-container orchestration
3. ✅ **Kubernetes** - Container orchestration platform
4. ✅ **Prometheus** - Metrics collection
5. ✅ **Grafana** - Metrics visualization
6. ✅ **PHPMyAdmin** - Database management
7. ✅ **MySQL** - Database system

## Tesztelés

### Gyors API Teszt

```bash
# Minden endpoint tesztelése
./test-api.sh  # Ha van test script

# Vagy manuálisan:
curl http://localhost:8000/api
curl http://localhost:8000/api/health
curl http://localhost:8000/api/metrics
curl http://localhost:8000/api/todos
```

### Kubernetes Teszt

```bash
# Pod-ok állapota
kubectl get pods -n php-todo-app

# Service endpoint teszt
curl $(minikube service php-todo-api-service -n php-todo-app --url)/api

# Logok
kubectl logs -f -n php-todo-app -l app=php-todo-api
```

## További Dokumentáció

Részletes projekt dokumentáció: `DOCUMENTATION.md`

A dokumentáció tartalmazza:
- Teljes architektúra leírás
- Részletes telepítési útmutató
- DevOps workflow magyarázat
- Védési pontok és válaszok
- Troubleshooting guide

## Licenc

MIT License - Oktatási célokra készült projekt

## Kapcsolat

**Készítette:**  Tatár Liliána
**Kurzus:** Felhő és DevOps alapok
**Technológia:** Plain PHP + MySQL + Docker + Kubernetes
**Dátum:** 2025 November

---

**Projekt célja:** Modern DevOps gyakorlatok bemutatása egy egyszerű, de teljes körű TODO alkalmazáson keresztül, Laravel nélkül, tisztán PHP-ban.

<!-- Develop branch teszt módosítás -->

<!-- Main branch teszt módosítás -->
