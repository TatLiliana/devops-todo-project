# Beadandó Projekt - Teljes Indítási Útmutató

> **DevOps TODO API - Plain PHP + MySQL + Docker + Kubernetes**
> Lépésről lépésre: A-tól Z-ig minden, ami szükséges a projekt futtatásához

---

## 📋 Tartalomjegyzék

1. [Előfeltételek telepítése](#1-előfeltételek-telepítése)
2. [Projekt letöltése](#2-projekt-letöltése)
3. [Gyors indítás Docker Compose-zal](#3-gyors-indítás-docker-compose-zal)
4. [API tesztelése](#4-api-tesztelése)
5. [Monitoring eszközök használata](#5-monitoring-eszközök-használata)
6. [CI/CD Pipeline megtekintése](#6-cicd-pipeline-megtekintése)
7. [Kubernetes deployment (opcionális)](#7-kubernetes-deployment-opcionális)
8. [Hibaelhárítás](#8-hibaelhárítás)
9. [Projekt leállítása](#9-projekt-leállítása)

---

## 1. Előfeltételek telepítése

### 1.1 Docker Desktop

**Windows:**
1. Látogasd meg: https://www.docker.com/products/docker-desktop/
2. Töltsd le a **Docker Desktop for Windows** verziót
3. Telepítsd a letöltött fájlt (DockerDesktopInstaller.exe)
4. Indítsd újra a számítógépet (ha kéri)
5. Nyisd meg a Docker Desktop alkalmazást
6. Várd meg, amíg a Docker elindult (az ikon zöldre vált az alsó sorban)

**Ellenőrzés:**
```bash
# Nyiss egy terminált (Command Prompt vagy PowerShell) és futtasd:
docker --version
docker-compose --version
```

Várt kimenet:
```
Docker version 24.0.x, build xxxxx
Docker Compose version v2.x.x
```

### 1.2 Git (ha még nincs telepítve)

**Windows:**
1. Látogasd meg: https://git-scm.com/download/win
2. Töltsd le és telepítsd
3. Alapértelmezett beállításokkal működik

**Ellenőrzés:**
```bash
git --version
```

Várt kimenet:
```
git version 2.x.x
```

### 1.3 Opcionális: Minikube (Kubernetes teszteléshez)

**Ha szeretnéd a Kubernetes deployment-et is kipróbálni:**
1. Látogasd meg: https://minikube.sigs.k8s.io/docs/start/
2. Töltsd le Windows verziónak megfelelőt
3. Telepítsd a kubectl-t is: https://kubernetes.io/docs/tasks/tools/install-kubectl-windows/

---

## 2. Projekt letöltése

### 2.1 Repository klónozása

Nyiss egy terminált (PowerShell vagy Command Prompt) és navigálj a kívánt mappába:

```bash
# Navigálj a kívánt helyre, például:
cd C:\Users\<felhasználónév>\Documents

# Klónozd a repository-t
git clone https://github.com/TatLiliana/devops-todo-project.git

# Lépj be a projekt mappába
cd devops-todo-project
```

### 2.2 Projekt struktúra áttekintése

```
devops-todo-project/
├── public/              # PHP alkalmazás belépési pont
├── src/                 # Controller-ek (TODO, Health, Metrics)
├── database/            # MySQL schema
├── k8s/                 # Kubernetes manifests
├── .github/workflows/   # CI/CD pipeline-ok
├── prometheus/          # Prometheus konfiguráció
├── grafana/             # Grafana beállítások
├── Dockerfile           # Docker image build
├── docker-compose.yml   # Teljes stack orchestration
└── README.md            # Projekt dokumentáció
```

---

## 3. Gyors indítás Docker Compose-zal

### 3.1 Docker szolgáltatások indítása

**Egyetlen parancs a teljes alkalmazás indítására:**

```bash
docker-compose up -d
```

**Mit csinál ez a parancs?**
- Elindítja a **PHP TODO API** alkalmazást (port 8000)
- Elindítja a **MySQL** adatbázist (port 3306)
- Elindítja a **PHPMyAdmin**-t (port 8080)
- Elindítja a **Prometheus**-t (port 9090)
- Elindítja a **Grafana**-t (port 4000)
- Automatikusan létrehozza az adatbázis táblákat
- Betölt példa TODO-kat

**Várt kimenet:**
```
[+] Running 6/6
 ✔ Network devops-todo-project_default    Created
 ✔ Container todo-mysql                   Started
 ✔ Container todo-phpmyadmin              Started
 ✔ Container todo-app                     Started
 ✔ Container todo-prometheus              Started
 ✔ Container todo-grafana                 Started
```

### 3.2 Ellenőrzés: Container-ek futnak-e?

```bash
docker-compose ps
```

**Várt kimenet:** Minden service STATE-je `running` legyen:
```
NAME                 STATUS
todo-app             Up
todo-mysql           Up (healthy)
todo-phpmyadmin      Up
todo-prometheus      Up
todo-grafana         Up
```

### 3.3 Elérhető szolgáltatások

Nyisd meg böngészőben az alábbi URL-eket:

| Szolgáltatás | URL | Leírás |
|--------------|-----|--------|
| **PHP TODO API** | http://localhost:8000/api | REST API főoldal |
| **PHPMyAdmin** | http://localhost:8080 | Adatbázis kezelő (root/rootpass) |
| **Prometheus** | http://localhost:9090 | Metrika gyűjtő |
| **Grafana** | http://localhost:4000 | Dashboard (admin/admin) |

**Első lépés:** Nyisd meg http://localhost:8000/api böngészőben

Várt válasz (JSON):
```json
{
  "message": "DevOps TODO API - Plain PHP Version",
  "version": "1.0.0",
  "endpoints": {
    "health": "/api/health",
    "metrics": "/api/metrics",
    "todos": "/api/todos"
  }
}
```

✅ **Ha ezt látod, az alkalmazás sikeresen elindult!**

---

## 4. API tesztelése

### 4.1 Health Check

**Böngészőben:**
```
http://localhost:8000/api/health
```

**Vagy PowerShell-ben:**
```powershell
Invoke-RestMethod -Uri "http://localhost:8000/api/health" -Method Get | ConvertTo-Json
```

**Várt válasz:**
```json
{
  "status": "healthy",
  "database": "connected",
  "timestamp": "2025-11-28 14:30:00",
  "uptime": "120 seconds"
}
```

### 4.2 TODO lista lekérése

**Böngészőben:**
```
http://localhost:8000/api/todos
```

**PowerShell:**
```powershell
Invoke-RestMethod -Uri "http://localhost:8000/api/todos" -Method Get | ConvertTo-Json
```

**Várt válasz:** Lista a példa TODO-kkal (3-5 darab)

### 4.3 Új TODO létrehozása

**PowerShell:**
```powershell
$body = @{
    title = "Teszt TODO"
    description = "Ez egy teszt feladat"
    priority = "high"
    completed = $false
} | ConvertTo-Json

Invoke-RestMethod -Uri "http://localhost:8000/api/todos" `
    -Method Post `
    -ContentType "application/json; charset=utf-8" `
    -Body ([System.Text.Encoding]::UTF8.GetBytes($body)) `
    | ConvertTo-Json
```

**Vagy használd az automatikus teszt scriptet:**
```powershell
.\POWERSHELL-TEST.ps1
```

Ez a script **minden API endpoint-ot letestel** automatikusan:
- ✅ API info
- ✅ Health check
- ✅ TODO lista
- ✅ TODO létrehozás (completed = false és true)
- ✅ TODO módosítás
- ✅ TODO toggle
- ✅ TODO törlés

### 4.4 cURL használata (Linux/Mac/Git Bash)

```bash
# Health check
curl http://localhost:8000/api/health

# TODO lista
curl http://localhost:8000/api/todos

# Új TODO
curl -X POST http://localhost:8000/api/todos \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Teszt TODO",
    "description": "Leírás",
    "priority": "medium",
    "completed": false
  }'
```

---

## 5. Monitoring eszközök használata

### 5.1 PHPMyAdmin - Adatbázis kezelő

**URL:** http://localhost:8080

**Bejelentkezés:**
- Szerver: `mysql`
- Felhasználó: `root`
- Jelszó: `rootpass`

**Mit tudsz csinálni?**
1. Bal oldalt válaszd ki: `todoapp` adatbázis
2. Kattints a `todos` táblára
3. Böngészd a létrehozott TODO-kat
4. Futass SQL query-ket:
   ```sql
   SELECT * FROM todos WHERE completed = 1;
   SELECT * FROM todos WHERE priority = 'high';
   ```

### 5.2 Prometheus - Metrika gyűjtés

**URL:** http://localhost:9090

**Mit tudsz csinálni?**
1. Kattints fent a **Graph** fülre
2. Írd be a keresőbe:
   ```
   total_todos
   ```
3. Nyomj **Execute**-ot
4. Váltsd át **Graph** nézetre

**Hasznos metrikák kipróbálásra:**
```promql
# Összes TODO
total_todos

# Aktív (nem befejezett) TODO-k
active_todos

# Befejezett TODO-k
completed_todos

# Magas prioritású TODO-k száma
todos_by_priority{priority="high"}

# PHP memória használat
php_memory_usage_bytes

# HTTP kérések száma
http_requests_total
```

### 5.3 Grafana - Vizualizáció

**URL:** http://localhost:4000

**Bejelentkezés:**
- Felhasználó: `admin`
- Jelszó: `admin`
- (Első bejelentkezéskor kérni fogja jelszó változtatást - átugorhatod: **Skip**)

**Dashboard létrehozása:**
1. Kattints bal oldalt a **+** ikonra → **Create Dashboard**
2. **Add visualization** → Válaszd a **Prometheus** data source-ot
3. Alul a query mezőbe írd: `active_todos`
4. Jobb oldalt válassz **Stat** vagy **Time series** vizualizációt
5. Kattints **Apply**
6. Hozzáadhatsz több panel-t is:
   - `completed_todos`
   - `todos_by_priority{priority="high"}`
   - `php_memory_usage_bytes`

**Kész dashboard mentése:**
- Kattints fent a **Save dashboard** ikonra (💾)
- Adj nevet: "TODO Monitoring"

---

## 6. CI/CD Pipeline megtekintése

### 6.1 GitHub Actions elérése

**URL:** https://github.com/TatLiliana/devops-todo-project/actions

**Mit látsz?**
- **CI - Build and Test**: Minden push és PR esetén fut (main, develop branch-eken)
- **CD - Deploy**: Csak main branch push esetén fut

### 6.2 CI Pipeline részletei

**Trigger események:**
- ✅ Push to `main` → CI fut
- ✅ Push to `develop` → CI fut
- ✅ Pull Request → CI fut

**Mit csinál a CI?**
1. **PHP 8.2 tesztek** (párhuzamos job)
   - MySQL service indítás
   - PHP beépített szerver indítás
   - API endpoint tesztek (health, metrics, todos)
2. **PHP 8.3 tesztek** (párhuzamos job)
   - Ugyanazok a tesztek PHP 8.3 verzióval
3. **Docker Build & Test**
   - Docker image build Buildx-szel
   - Container indítási teszt
   - Működés validálás

**Megtekintés:**
1. Menj a GitHub Actions oldalra
2. Kattints egy futásra (pl. "CI - Build and Test #12")
3. Látod a 3 job-ot: "Test on PHP 8.2", "Test on PHP 8.3", "Build Docker Image"
4. Kattints bármelyikre → látod a részletes logokat

### 6.3 CD Pipeline részletei

**Trigger:** Csak `main` branch push esetén

**Mit csinál a CD?**
1. Docker image build
2. Docker Hub login
3. Push image-et Docker Hub-ra:
   - Tag: `lilianat28/php-todo-api:latest`
   - Tag: `lilianat28/php-todo-api:sha-xxxxxxx`

**Docker Hub ellenőrzés:**
1. Menj a Docker Hub-ra: https://hub.docker.com/r/lilianat28/php-todo-api
2. Látod a legújabb push-olt image-eket

### 6.4 Branch stratégia

| Branch | CI futás? | CD futás? | Leírás |
|--------|-----------|-----------|--------|
| `main` | ✅ IGEN | ✅ IGEN | Production-ready kód, automatic deploy |
| `develop` | ✅ IGEN | ❌ NEM | Development/staging, csak tesztelés |
| Pull Request | ✅ IGEN | ❌ NEM | Code review előtt tesztelés |

**Részletek:** Lásd `PIPELINE-TRIGGERS.md` fájlt a projektben

---

## 7. Kubernetes Deployment (opcionális)

**Csak akkor kövesd, ha telepítve van a Minikube és kubectl!**

### 7.1 Minikube indítása

```bash
minikube start --cpus=4 --memory=4096
```

Várd meg, amíg elindul (1-2 perc).

### 7.2 Docker image build Minikube környezetben

```bash
# Konfiguráld a Docker CLI-t Minikube-ra
eval $(minikube docker-env)

# Build image (vagy PowerShell-ben: minikube docker-env | Invoke-Expression)
docker build -t php-todo-api:latest .
```

### 7.3 Kubernetes resources telepítése

```bash
# Namespace létrehozása
kubectl apply -f k8s/namespace.yml

# MySQL deployment
kubectl apply -f k8s/mysql.yml

# Várj, amíg MySQL elindul
kubectl wait --for=condition=ready pod -l app=mysql -n php-todo-app --timeout=120s

# PHP TODO API deployment és service
kubectl apply -f k8s/app-deployment.yml
kubectl apply -f k8s/app-service.yml
```

### 7.4 Ellenőrzés

```bash
# Pod-ok állapota
kubectl get pods -n php-todo-app

# Services
kubectl get services -n php-todo-app

# Service URL lekérése
minikube service php-todo-api-service -n php-todo-app --url
```

**Kimenet példa:**
```
http://192.168.49.2:30080
```

### 7.5 API tesztelése Kubernetes-en

```bash
# Mentsd el az URL-t változóba
$API_URL = minikube service php-todo-api-service -n php-todo-app --url

# Teszteld az API-t
curl $API_URL/api
curl $API_URL/api/health
curl $API_URL/api/todos
```

### 7.6 Kubernetes cleanup

```bash
# Összes resource törlése
kubectl delete namespace php-todo-app

# Minikube leállítása
minikube stop
```

---

## 8. Hibaelhárítás

### 8.1 "Docker daemon not running" hiba

**Probléma:** Docker Desktop nem fut

**Megoldás:**
1. Indítsd el a Docker Desktop alkalmazást
2. Várd meg, amíg az ikon zöldre vált
3. Futtasd újra: `docker-compose up -d`

### 8.2 "Port already in use" hiba

**Probléma:** A port már használatban van (pl. 8000, 3306, 8080)

**Ellenőrzés (PowerShell):**
```powershell
netstat -ano | findstr :8000
```

**Megoldás 1:** Állítsd le a foglalt portot használó alkalmazást

**Megoldás 2:** Módosítsd a portot `docker-compose.yml`-ben:
```yaml
services:
  app:
    ports:
      - "8001:80"  # Módosítsd 8000-ről 8001-re
```

### 8.3 MySQL connection error

**Probléma:** `SQLSTATE[HY000] [2002] Connection refused`

**Ellenőrzés:**
```bash
docker-compose ps
```

**Ha MySQL nem fut:**
```bash
docker-compose up -d mysql
docker-compose logs mysql
```

**Ha látod: `[Server] /usr/sbin/mysqld: ready for connections`** → MySQL OK

**Újraindítás:**
```bash
docker-compose restart app
```

### 8.4 API 500 Internal Server Error

**Ellenőrzés:**
```bash
docker-compose logs app
```

**Keress PHP error-okat:**
- `PHP Parse error` → szintaxis hiba
- `SQLSTATE` → adatbázis hiba
- `Permission denied` → jogosultsági hiba

**Gyakori megoldás:**
```bash
docker-compose down
docker-compose up -d --build
```

### 8.5 Browser csak PHP kódot mutat

**Probléma:** Apache nem értelmezi a PHP fájlokat

**Ellenőrzés:**
```bash
docker exec -it todo-app php -v
```

**Megoldás:** Rebuild a Docker image-et:
```bash
docker-compose down
docker-compose build --no-cache
docker-compose up -d
```

### 8.6 Grafana nem tölt be

**Probléma:** Grafana port nem elérhető vagy timeout

**Ellenőrzés:**
```bash
docker-compose logs grafana
```

**Várd meg:** "HTTP Server Listen" üzenetet

**Próbáld újra:**
```bash
docker-compose restart grafana
```

### 8.7 CI/CD pipeline fail

**GitHub Actions hiba megtekintése:**
1. Menj: https://github.com/TatLiliana/devops-todo-project/actions
2. Kattints a failed run-ra (piros X)
3. Kattints a failed job-ra
4. Nézd meg a részletes logokat

**Gyakori okok:**
- **MySQL timeout:** Várj tovább (increase `--health-retries`)
- **Docker build failed:** Ellenőrizd a Dockerfile-t
- **API test failed:** Ellenőrizd az endpoint-okat lokálisan

---

## 9. Projekt leállítása

### 9.1 Docker Compose services leállítása

**Leállítás (container-ek megőrzése):**
```bash
docker-compose stop
```

**Újraindítás:**
```bash
docker-compose start
```

### 9.2 Teljes cleanup (minden törlése)

**Container-ek és network törlése:**
```bash
docker-compose down
```

**Container-ek, network ÉS adatbázis törlése:**
```bash
docker-compose down -v
```

⚠️ **Figyelem:** A `-v` flag törli az összes adatot a MySQL adatbázisból!

### 9.3 Docker image-ek törlése

```bash
# Projekt image-ek listázása
docker images | grep todo

# Törlés
docker rmi todo-app todo-mysql todo-prometheus todo-grafana
```

---

## 🎯 Gyors Referencia - Legfontosabb parancsok

### Indítás
```bash
docker-compose up -d          # Minden service indítása
docker-compose ps             # Futó szolgáltatások ellenőrzése
```

### Tesztelés
```bash
curl http://localhost:8000/api/health           # Health check
curl http://localhost:8000/api/todos            # TODO lista
.\POWERSHELL-TEST.ps1                            # Teljes API teszt
```

### Logok
```bash
docker-compose logs app       # PHP alkalmazás logok
docker-compose logs mysql     # MySQL logok
docker-compose logs -f app    # Real-time app logok
```

### Leállítás
```bash
docker-compose down           # Leállítás
docker-compose down -v        # Leállítás + adatok törlése
```

---

## 📚 További Dokumentáció

| Fájl | Tartalom |
|------|----------|
| `README.md` | Projekt áttekintés, funkcionalitás, technológiai stack |
| `DOCUMENTATION.md` | Részletes architektúra, védési pontok |
| `PIPELINE-TRIGGERS.md` | CI/CD trigger magyarázat, branch stratégia |
| `API-EXAMPLES.md` | Teljes API dokumentáció cURL és PowerShell példákkal |
| `CHECK-GITHUB-SECRETS.md` | Docker Hub credentials beállítása |

---

## ✅ Projekt Követelmények Teljesítése

### Code (10 pont)
- ✅ Tiszta PHP kód (MVC pattern)
- ✅ REST API best practices
- ✅ Adatbázis kapcsolat (PDO)
- ✅ Environment változók
- ✅ Error handling

### Build & Test (15 pont)
- ✅ CI pipeline (GitHub Actions)
- ✅ Multi-version testing (PHP 8.2, 8.3)
- ✅ Docker build automation
- ✅ API endpoint tesztek
- ✅ Health check

### Release & Deploy (15 pont)
- ✅ Docker konténerizáció
- ✅ Docker Compose orchestration
- ✅ Kubernetes deployment
- ✅ CD pipeline (Docker Hub)
- ✅ Automatikus verziókezelés

### Monitor & Feedback (10 pont)
- ✅ Prometheus metrikák
- ✅ Grafana vizualizáció
- ✅ Health check endpoint
- ✅ Application metrics
- ✅ Database monitoring

### DevOps Tool-ok (5+ darab, Git és Docker nem számít)
1. ✅ **GitHub Actions** - CI/CD
2. ✅ **Docker Compose** - Orchestration
3. ✅ **Kubernetes** - Container orchestration
4. ✅ **Prometheus** - Monitoring
5. ✅ **Grafana** - Visualization
6. ✅ **PHPMyAdmin** - Database UI
7. ✅ **MySQL** - Database

---

## 💡 Hasznos Tippek

### 1. Első használatkor
- Várj 30 másodpercet, amíg minden service elindul
- Először a health check-et nézd meg: http://localhost:8000/api/health
- PHPMyAdmin-ban ellenőrizd az adatbázist

### 2. Development workflow
- `develop` branch-en dolgozz fejlesztéskor
- Pull Request → CI automatikusan fut
- Merge to `main` → CD automatikusan deploy-ol

### 3. Monitoring
- Prometheus: Metrikák gyűjtése 15 másodpercenként
- Grafana: Dashboard-ok mentése későbbi használatra
- PHPMyAdmin: SQL query-k futtatása debug-hoz

### 4. Troubleshooting
- Mindig először: `docker-compose logs app`
- Ha nem működik: `docker-compose down && docker-compose up -d`
- Ha még mindig nem: `docker-compose build --no-cache && docker-compose up -d`

---

## 📞 Kapcsolat

**Készítette:** Tatár Liliána
**Kurzus:** Felhő és DevOps alapok
**GitHub:** https://github.com/Tatliliana/devops-todo-project
**Docker Hub:** https://hub.docker.com/r/lilianat28/php-todo-api

---

**Utolsó frissítés:** 2025 November
**Verzió:** 1.0.0

---

## ⭐ Projekt Állapot Ellenőrzés

**Ha minden működik, az alábbi URL-ek elérhetőek:**

- ✅ http://localhost:8000/api → API információk
- ✅ http://localhost:8000/api/health → `"status": "healthy"`
- ✅ http://localhost:8000/api/todos → TODO lista
- ✅ http://localhost:8080 → PHPMyAdmin bejelentkezési oldal
- ✅ http://localhost:9090 → Prometheus UI
- ✅ http://localhost:4000 → Grafana bejelentkezési oldal

**Ha mindegyik működik: 🎉 A projekt sikeresen fut!**

---

**Jó tesztelést! 🚀**
