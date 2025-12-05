# DevOps TODO API - Videó Bemutató Forgatókönyv

> Teljes projekt bemutató: Repó lehúzásától a monitoring rendszerek bemutatásáig

---

## 📋 Videó Struktúra Áttekintés

1. **Bevezető** - Projekt bemutatása
2. **Repository lehúzása és környezet ellenőrzése**
3. **Docker Compose indítás és ellenőrzés**
4. **API működés bemutatása**
5. **Git workflow - Develop branch**
6. **Git workflow - Main branch**
7. **Pull Request workflow**
8. **CI/CD pipeline-ok bemutatása**
9. **Docker Hub ellenőrzése**
10. **Változások lokális alkalmazása (docker-compose pull)**
11. **Adatbázis bemutatása (PHPMyAdmin)**
12. **Prometheus bemutatása**
13. **Grafana bemutatása**
14. **Kubernetes előkészítettség**
15. **Összefoglalás**

---

## 1. Bevezető - Projekt Bemutatása

### Lépések:
- Nyisd meg a böngészőt a GitHub repository-val

### Parancsok:
```
Nincs parancs, csak bemutató
```

### Bemondó szöveg:
"A mai bemutatóban a DevOps TODO API projektemet fogom végigvezetni. Ez egy Plain PHP-ban írt REST API alkalmazás, amely egy teljes körű DevOps környezettel van ellátva. A projekt tartalmaz Docker konténerizációt, Kubernetes orchestration-t, CI/CD pipeline-okat GitHub Actions-zel, valamint Prometheus és Grafana monitoring rendszereket. A bemutatóban végigmegyünk a repository lehúzásától kezdve a változtatások pusholásán keresztül, megmutatom mikor fut a CI és mikor a CD pipeline, és bemutatom az összes monitoring eszközt is. Kezdjük is!"

---

## 2. Repository Lehúzása és Környezet Ellenőrzése

### Lépések:
1. Nyiss egy PowerShell vagy Command Prompt terminált
2. Navigálj egy kívánt mappába
3. Klónozd le a repository-t
4. Lépj be a projekt mappába
5. Ellenőrizd a Docker Desktop futását

### Parancsok:
```powershell
# Navigálás a kívánt mappába
cd C:\Users\<felhasználónév>\Documents

# Repository klónozása
git clone https://github.com/TatLiliana/devops-todo-project.git

# Belépés a projekt mappába
cd devops-todo-project

# Git branch-ek ellenőrzése
git branch -a

# Docker verzió ellenőrzése
docker --version
docker-compose --version

# Docker Desktop futásának ellenőrzése
docker ps
```

### Bemondó szöveg:
"Először klónozzuk le a projektet a GitHubról. Megnyitok egy PowerShell terminált és navigálok egy megfelelő mappába. Most kiadom a git clone parancsot a repository URL-jével. A klónozás után belépek a projekt mappába. Ellenőrzöm, hogy milyen branch-ek léteznek - látható a main és develop branch is, ez lesz fontos később a CI/CD bemutatásnál. Ellenőrzöm a Docker és Docker Compose verzióját is, hogy minden rendben van-e. A Docker Desktop fut, így készen állunk a projekt indítására."

---

## 3. Docker Compose Indítás és Ellenőrzés

### Lépések:
1. Indítsd el az összes szolgáltatást Docker Compose-zal
2. Ellenőrizd a futó konténereket
3. Nézd meg a logokat

### Parancsok:
```powershell
# Összes szolgáltatás indítása (háttérben)
docker-compose up -d

# Várakozás (30 másodperc) amíg minden elindul
Start-Sleep -Seconds 30

# Futó konténerek ellenőrzése
docker-compose ps

# Alkalmazás logok megtekintése
docker-compose logs app --tail=20

# MySQL logok megtekintése
docker-compose logs mysql --tail=10
```

### Bemondó szöveg:
"Most elindítjuk az egész alkalmazást egyetlen paranccsal: docker-compose up -d. A -d flag azt jelenti, hogy háttérben fog futni. Ez a parancs elindítja az összes szükséges szolgáltatást: a PHP TODO API-t a 8000-es porton, a MySQL adatbázist, a PHPMyAdmin-t, a Prometheus-t és a Grafana-t is. Várunk körülbelül 30 másodpercet, hogy minden konténer teljesen elinduljon. Most ellenőrizzük a futó konténereket a docker-compose ps paranccsal. Látható, hogy minden szolgáltatás 'Up' státuszban van, ami azt jelenti, hogy sikeresen elindultak. Nézzük meg az alkalmazás logokat is, hogy minden rendben van-e. A logokból látható, hogy az Apache webszerver fut és az alkalmazás készen áll a kérések fogadására."

---

## 4. API Működés Bemutatása

### Lépések:
1. Nyisd meg böngészőben az API-t
2. Tesztelj PowerShell parancsokkal
3. Hozz létre új TODO-kat

### Parancsok:
```powershell
# API információk lekérése
Invoke-RestMethod -Uri "http://localhost:8000/api" -Method Get | ConvertTo-Json

# Health check
Invoke-RestMethod -Uri "http://localhost:8000/api/health" -Method Get | ConvertTo-Json

# TODO lista lekérése
Invoke-RestMethod -Uri "http://localhost:8000/api/todos" -Method Get | ConvertTo-Json

# Új TODO létrehozása
$body = @{
    title = "Videó bemutató TODO"
    description = "Ez a TODO a videó bemutatóhoz készült"
    priority = "high"
    completed = $false
} | ConvertTo-Json

Invoke-RestMethod -Uri "http://localhost:8000/api/todos" `
    -Method Post `
    -ContentType "application/json; charset=utf-8" `
    -Body ([System.Text.Encoding]::UTF8.GetBytes($body)) `
    | ConvertTo-Json

# TODO lista újra (új TODO-val)
Invoke-RestMethod -Uri "http://localhost:8000/api/todos" -Method Get | ConvertTo-Json
```

### Böngésző URL-ek:
```
http://localhost:8000/api
http://localhost:8000/api/health
http://localhost:8000/api/todos
```

### Bemondó szöveg:
"Most teszteljük az API működését. Először megnyitom böngészőben az API root endpoint-ot a localhost 8000-es porton. Látható az API információs oldal, amely listázza az összes elérhető végpontot. Most PowerShell-ben is letesztelem az endpoint-okat. Az API info endpoint visszaadja az alkalmazás verzióját és az elérhető végpontokat. A health check endpoint mutatja, hogy az alkalmazás egészséges, az adatbázis kapcsolat működik, és látható a PHP verzió valamint a memória használat is. Most lekérem a TODO listát - látható néhány példa TODO, amit az adatbázis inicializálás során hoztunk létre. Hozok létre egy új TODO-t is PowerShell-ből: beállítom a címet 'Videó bemutató TODO', hozzáadok egy leírást, beállítom magas prioritásúra és befejezetlen státuszba. A létrehozás sikeres volt, visszakaptuk az új TODO összes adatát, beleértve az automatikusan generált ID-t és timestamp-eket is. Ha újra lekérem a TODO listát, látható az új elem is."

---

## 5. Git Workflow - Develop Branch

### Lépések:
1. Váltás develop branch-re
2. Módosítás egy fájlban (pl. README.md)
3. Commit és push develop-ra
4. GitHub Actions ellenőrzése

### Parancsok:
```powershell
# Váltás develop branch-re
git checkout develop

# Aktuális branch ellenőrzése
git branch

# README.md módosítása (pl. egy komment hozzáadása az elejére)
# Ezt manuálisan csináld egy szövegszerkesztőben, vagy:
Add-Content -Path README.md -Value "`n<!-- Develop branch teszt módosítás -->" -Encoding UTF8

# Git status ellenőrzése
git status

# Módosítások hozzáadása
git add README.md

# Commit készítése
git commit -m "Test: Add develop branch modification for demo"

# Push develop branch-re
git push origin develop
```

### GitHub Actions ellenőrzés:
```
URL: https://github.com/TatLiliana/devops-todo-project/actions
```

### Bemondó szöveg:
"Most bemutatom a Git workflow-t és a CI/CD pipeline-ok működését. Először a develop branch-re váltok a git checkout develop paranccsal. Ellenőrzöm, hogy tényleg a develop branch-en vagyok. Most módosítok egy fájlt, például hozzáadok egy kommentet a README.md elejére. A git status parancs mutatja, hogy a README.md módosult. Hozzáadom a változtatást a staging area-hoz git add-dal, majd létrehozok egy commit-ot értelmes üzenettel: 'Test: Add develop branch modification for demo'. Most push-olom a változtatást a develop branch-re a git push origin develop paranccsal. Amint a push megtörtént, megnyitom a GitHub Actions oldalt. Látható, hogy automatikusan elindult egy új workflow futás. Ez a CI pipeline, amely a develop branch-re való push hatására indult el. A CD pipeline NEM fut, mert az csak a main branch-re való push esetén aktiválódik. A CI pipeline most fut: láthatóak a párhuzamos job-ok, a PHP 8.2-es és 8.3-as tesztek, valamint a Docker build job is. Várjuk meg, amíg befejezi - a zöld pipa jelzi, hogy minden teszt sikeresen lefutott."

---

## 6. Git Workflow - Main Branch Push (CI + CD)

### Lépések:
1. Váltás main branch-re
2. Develop merge main-be vagy új módosítás
3. Commit és push main-ra
4. GitHub Actions ellenőrzése - CI ÉS CD is fut

### Parancsok:
```powershell
# Váltás main branch-re
git checkout main

# Develop branch merge-elése main-be
git pull origin main
git merge develop

# VAGY egyszerű módosítás (ha nem akarod merge-elni):
Add-Content -Path README.md -Value "`n<!-- Main branch teszt módosítás -->" -Encoding UTF8

# Git status
git status

# Add és commit
git add .
git commit -m "Test: Main branch modification for demo - triggers CI and CD"

# Push main branch-re
git push origin main
```

### GitHub Actions ellenőrzés:
```
URL: https://github.com/TatLiliana/devops-todo-project/actions
```

### Bemondó szöveg:
"Most váltok a main branch-re a git checkout main paranccsal. Itt két opció van: vagy merge-elem a develop branch-et a main-be, vagy egyszerűen készítek egy új módosítást. Most egy egyszerű módosítást csinálok a README fájlban a bemutató kedvéért. Git status mutatja a módosítást. Hozzáadom és commit-olom értelmes üzenettel, amely egyértelműen jelzi, hogy ez a main branch módosítás CI-t ÉS CD-t is triggerel. Most push-olom a main branch-re. Figyeljük meg a GitHub Actions-t! Látható, hogy MOST kétféle workflow is elindult: az egyik a CI - Build and Test, a másik pedig a CD - Deploy. Ezek párhuzamosan futnak. A CI pipeline ugyanazokat a teszteket futtatja, mint a develop branch esetén: PHP 8.2 és 8.3 tesztek, Docker build. A CD pipeline viszont csak main branch push esetén fut: build-eli a Docker image-et és feltölti a Docker Hub-ra a latest és commit SHA tag-ekkel. Ez biztosítja, hogy minden main branch-re kerülő változtatás automatikusan production-ready image-ként elérhető lesz a Docker Hub-on. Várjuk meg, amíg mindkét pipeline sikeresen lefut."

---

## 7. Pull Request Workflow

### Lépések:
1. Hozz létre egy feature branch-et
2. Módosítás és push
3. Pull Request nyitása GitHub-on
4. CI futás megfigyelése
5. PR merge

### Parancsok:
```powershell
# Feature branch létrehozása
git checkout -b feature/demo-feature

# Módosítás
Add-Content -Path README.md -Value "`n<!-- Feature branch teszt -->" -Encoding UTF8

# Commit és push
git add .
git commit -m "Feature: Add demo feature for pull request demo"
git push origin feature/demo-feature
```

### GitHub-on:
```
1. Menj a repository-ra: https://github.com/TatLiliana/devops-todo-project
2. Kattints "Compare & pull request"
3. Base: main, Compare: feature/demo-feature
4. Create pull request
5. Figyeld a CI futást
6. Merge pull request (ha CI sikeres)
```

### Bemondó szöveg:
"Most bemutatom a Pull Request workflow-t, amely a professional fejlesztési gyakorlatok alapja. Létrehozok egy új feature branch-et git checkout -b paranccsal feature/demo-feature néven. Ezen a branch-en készítek egy módosítást, commit-olom és push-olom a remote repository-ba. Most megnyitom a GitHub-ot. Látható, hogy a GitHub észlelte az új branch-et és felajánlja a Pull Request létrehozását. Kattintok a 'Compare & pull request' gombra. Beállítom, hogy a base branch a main legyen, és a compare branch pedig a feature/demo-feature. Hozzáadok egy leírást, majd létrehozom a Pull Request-et. Figyeljük meg, hogy AZONNAL elindul a CI pipeline! Ez azért fontos, mert még merge előtt teszteli a kódot. Ha a CI fail-elne, nem tudnánk merge-elni, így védve a main branch-et a hibás kódtól. A CD pipeline viszont NEM fut, mert ez még csak egy Pull Request, nem tényleges merge a main-re. Várjuk meg a CI futás végét. Látható a zöld pipa: minden teszt sikeres volt. Most nyugodtan merge-elhetem a Pull Request-et. Miután a merge megtörtént, AKKOR indul el a CD pipeline is, mert a merge hatására a main branch-re kerül új commit. Ez a folyamat biztosítja a kód minőségét és az automatikus deployment-et."

---

## 8. CI/CD Pipeline-ok Részletes Bemutatása

### Lépések:
1. GitHub Actions oldal megnyitása
2. CI pipeline részleteinek bemutatása
3. CD pipeline részleteinek bemutatása
4. Workflow fájlok bemutatása

### GitHub Actions:
```
URL: https://github.com/TatLiliana/devops-todo-project/actions
```

### Fájlok megtekintése:
```powershell
# CI workflow megtekintése
Get-Content .github/workflows/ci.yml

# CD workflow megtekintése
Get-Content .github/workflows/cd.yml
```

### Bemondó szöveg:
"Most részletesen bemutatom a CI/CD pipeline-ok felépítését. A GitHub Actions oldalon látható az összes workflow futás. A CI - Build and Test pipeline három párhuzamos job-ból áll: az első job PHP 8.2 verzióval teszteli az alkalmazást, a második job PHP 8.3-mal, a harmadik pedig a Docker image build-et végzi. Kattintok egy sikeres CI futásra és megnézem a részleteket. A PHP 8.2 job-ban látható, hogy elindul a MySQL service, majd a PHP beépített szerver, és végül különböző API endpoint-ok tesztelése történik: health check, metrics, todos API. Ugyanez fut le PHP 8.3 verziónál is, így biztosítva a multi-version kompatibilitást. A Docker build job pedig build-eli az image-et és teszteli a konténer működését. A CD - Deploy pipeline csak main branch push esetén fut. Itt látható a Docker Buildx setup, a Docker Hub-ra való login, és a multi-tag image push: latest tag, commit SHA tag és branch tag. Most megnézem a workflow fájlokat is. A .github/workflows/ci.yml fájlban látható a trigger konfiguráció: push és pull_request események a main és develop branch-ekre. A matrix strategy biztosítja a multi-version tesztelést. A cd.yml fájlban pedig csak a main branch push van trigger-ként beállítva, és itt történik a Docker Hub deploy."

---

## 9. Docker Hub Ellenőrzése

### Lépések:
1. Docker Hub megnyitása
2. Image-ek ellenőrzése
3. Tag-ek bemutatása

### Docker Hub:
```
URL: https://hub.docker.com/r/lilianat28/php-todo-api
```

### Bemondó szöveg:
"Most ellenőrizzük a Docker Hub-ot, hogy a CD pipeline valóban feltöltötte-e az image-eket. Megnyitom a Docker Hub-ot és bejelentkezem. Navigálok a php-todo-api repository-mhoz. Itt láthatók az összes push-olt image-ek különböző tag-ekkel. A 'latest' tag mindig a legfrissebb main branch build-et jelenti. Láthatók a commit SHA alapú tag-ek is, például 'sha-abc123', amelyek konkrét commit-okhoz tartoznak. Ez lehetővé teszi, hogy bármikor visszatérhessünk egy konkrét verzióhoz. A 'main' tag pedig mindig a main branch legfrissebb állapotát jelöli. Minden image mérete látható, valamint a push időpontja is. Ez az automatikus deployment azt jelenti, hogy bárki bárhol a világon egyszerűen futtathatja a legfrissebb verziót egy docker pull paranccsal."

---

## 10. Változások Lokális Alkalmazása (docker-compose pull & up)

### Lépések:
1. Pull legfrissebb image-ek Docker Hub-ról
2. Újraindítás a friss image-ekkel
3. Ellenőrzés

### Parancsok:
```powershell
# Legfrissebb image-ek letöltése Docker Hub-ról
docker-compose pull

# Szolgáltatások újraindítása a friss image-ekkel
docker-compose up -d

# Ellenőrzés: melyik image-et használja
docker-compose images

# Alkalmazás újra tesztelése
Invoke-RestMethod -Uri "http://localhost:8000/api/health" -Method Get | ConvertTo-Json
```

### Bemondó szöveg:
"Most bemutatom, hogyan lehet lokálisan alkalmazni egy új deploy után a változásokat. Ha egy új verzió feltöltődött a Docker Hub-ra és mi szeretnénk a legfrissebb verziót futtatni, két parancsra van szükség. Először kiadom a docker-compose pull parancsot, amely letölti a Docker Hub-ról az összes image legfrissebb verzióját a docker-compose fájlban meghatározott service-ekhez. Látható, hogy a pull folyamat fut. Amint a pull befejeződött, kiadom a docker-compose up -d parancsot, amely újraindítja a szolgáltatásokat az új image-ekkel. A docker-compose images paranccsal ellenőrzöm, hogy melyik image verziókat használja jelenleg a rendszer. Látható az image ID és a repository:tag információ. Most tesztelem újra az API-t, hogy minden működik-e a friss verzióval. A health check sikeres, az alkalmazás fut az új image-el. Ez a folyamat biztosítja, hogy production környezetben is könnyedén tudjunk új verziókra frissíteni."

---

## 11. Adatbázis Bemutatása (PHPMyAdmin)

### Lépések:
1. PHPMyAdmin megnyitása
2. Bejelentkezés
3. Adatbázis struktúra bemutatása
4. TODO-k megtekintése
5. SQL query futtatása

### PHPMyAdmin:
```
URL: http://localhost:8080
Username: root
Password: rootpass
```

### SQL Query példák:
```sql
-- Összes TODO
SELECT * FROM todos;

-- Csak befejezettek
SELECT * FROM todos WHERE completed = 1;

-- Magas prioritásúak
SELECT * FROM todos WHERE priority = 'high';

-- TODO-k prioritás szerinti csoportosítása
SELECT priority, COUNT(*) as count FROM todos GROUP BY priority;
```

### Bemondó szöveg:
"Most bemutatom az adatbázist a PHPMyAdmin felületen keresztül. Megnyitom a böngészőben a localhost 8080-as portot. Bejelentkezem root felhasználóval, a jelszó 'rootpass'. A bal oldali menüben kiválasztom a todoapp adatbázist. Láthatók a táblák, jelenleg egy tábla van: a todos. Kattintok a todos táblára és a Browse fülre. Itt látható az összes TODO rekord az adatbázisban. Látszanak az oszlopok: id, title, description, completed, priority, due_date, created_at és updated_at. A completed mező boolean, 0 jelenti a befejezetlen, 1 a befejezett TODO-kat. A priority mező enum típusú low, medium vagy high értékekkel. Most futtatok néhány SQL query-t az SQL fülön. Először lekérem az összes TODO-t. Most csak a befejezett TODO-kat kérdezem le WHERE completed = 1 feltétellel. Most a magas prioritású TODO-kat. Végül csoportosítom a TODO-kat prioritás szerint és megszámolom őket GROUP BY használatával. Ez a query eredménye megegyezik azzal, amit a Prometheus metrikákban is látni fogunk. Az adatbázisban látható indexek is: idx_completed, idx_priority és idx_created_at, amelyek optimalizálják a gyakori lekérdezéseket."

---

## 12. Prometheus Bemutatása

### Lépések:
1. Prometheus UI megnyitása
2. Targets ellenőrzése
3. Metrikák lekérdezése
4. Graph nézet bemutatása

### Prometheus:
```
URL: http://localhost:9090
```

### Prometheus Query példák (PromQL):
```promql
# Összes TODO
total_todos

# Aktív TODO-k
active_todos

# Befejezett TODO-k
completed_todos

# Magas prioritású TODO-k
todos_by_priority{priority="high"}

# Összes prioritás
todos_by_priority

# PHP memória használat megabyte-ban
php_memory_usage_bytes / 1024 / 1024

# Befejezett TODO-k aránya
completed_todos / total_todos * 100
```

### Targets ellenőrzés:
```
Status → Targets
```

### Bemondó szöveg:
"Most bemutatom a Prometheus monitoring rendszert. Megnyitom a localhost 9090-es portot. Először ellenőrzöm a Targets menüpontot a Status menü alatt. Itt látható a php-todo-api job, amely az alkalmazásunk metrics endpoint-ját 15 másodpercenként scrapeli. A State 'UP', ami azt jelenti, hogy a Prometheus sikeresen kapcsolódik és gyűjti a metrikákat. Látható az endpoint címe: app:80/api/metrics, valamint az utolsó scrape időpontja és időtartama is. Most a Graph fülre váltok és kipróbálok néhány metrikát. Beírom a total_todos metrikát és Execute-olom. Ez mutatja az összes TODO számát. Kipróbálom az active_todos metrikát, amely a befejezetlen TODO-k számát mutatja. A completed_todos a befejezett TODO-kat. Most egy label-es metrikát próbálok: todos_by_priority magas prioritással. Ez csak a magas prioritású TODO-k számát adja vissza. Ha elhagyom a label filtert, látható mindhárom prioritás külön-külön. Kipróbálom a PHP memória metrikát is, megabyte-ra átszámítva osztással 1024-gyel kétszer. Végül kiszámolom a befejezett TODO-k arányát: completed_todos osztva total_todos-szal, szorozva 100-zal százalékos érték kapásához. Átváltok Graph nézetre, ahol látható az időbeli változás is. Prometheus 15 másodpercenként gyűjti ezeket az értékeket, így látható, ahogy a metrikák változnak amikor TODO-kat hozunk létre, módosítunk vagy törlünk."

---

## 13. Grafana Bemutatása

### Lépések:
1. Grafana megnyitása
2. Bejelentkezés
3. Data source ellenőrzése
4. Dashboard létrehozása
5. Panel-ek hozzáadása különböző metrikákkal
6. Dashboard mentése

### Grafana:
```
URL: http://localhost:4000
Username: admin
Password: admin
```

### Dashboard panel példák:
```
Panel 1: active_todos (Stat visualization)
Panel 2: completed_todos (Stat visualization)
Panel 3: todos_by_priority (Bar gauge)
Panel 4: php_memory_usage_bytes / 1024 / 1024 (Time series - MB-ban)
Panel 5: total_todos (Time series)
```

### Bemondó szöveg:
"Most bemutatom a Grafana vizualizációs rendszert. Megnyitom a localhost 4000-es portot. Bejelentkezem admin felhasználóval, a jelszó szintén admin. Az első bejelentkezésnél kéri a jelszó megváltoztatását, de ezt átugorhatjuk Skip gombbal. Először ellenőrzöm, hogy a Prometheus data source megfelelően be van-e állítva. Megyek a Configuration, majd Data Sources menüpontba. Látható a Prometheus data source, amely a http://prometheus:9090 címen érhető el. A Test gomb megnyomásával ellenőrzöm a kapcsolatot - Data source is working üzenet jelzi, hogy minden rendben van. Most létrehozok egy új Dashboard-ot. Kattintok a Create Dashboard gombra, majd Add visualization-re. Kiválasztom a Prometheus data source-ot. Az első panel-hez beírom az active_todos metrikát. Jobb oldalt választok Stat vizualizációt, amely egy nagy számmal jeleníti meg az aktuális értéket. Beállítom a panel címét 'Aktív TODO-k'-ra és Apply-olom. Hozzáadok egy második panel-t completed_todos metrikával, szintén Stat vizualizációval. Most hozzáadok egy Bar gauge típusú panel-t a todos_by_priority metrikához, amely oszlopdiagramként mutatja a három prioritás értékeit. Hozzáadok egy Time series panel-t is a PHP memória használathoz, megabyte-ban megjelenítve. Végül egy Time series panel-t a total_todos metrikához, ahol látható az időbeli változás. A Dashboard most már több panel-t tartalmaz, amelyek real-time-ban frissülnek. Mentem a Dashboard-ot 'TODO Monitoring' néven. Ezzel a Dashboard bármikor betölthető és követhető az alkalmazás állapota. A Grafana lehetőséget ad alert-ek beállítására is, például ha az aktív TODO-k száma meghalad egy bizonyos értéket."

---

## 14. Kubernetes Előkészítettség Bemutatása

### Lépések:
1. K8s manifest fájlok bemutatása
2. Magyarázat a deployment-ről
3. Minikube telepítés említése

### Fájlok megtekintése:
```powershell
# Kubernetes manifest fájlok listázása
Get-ChildItem k8s

# Namespace manifest
Get-Content k8s/namespace.yml

# MySQL StatefulSet
Get-Content k8s/mysql.yml

# App Deployment
Get-Content k8s/app-deployment.yml

# App Service
Get-Content k8s/app-service.yml
```

### Bemondó szöveg:
"A projekt tartalmaz Kubernetes deployment manifest fájlokat is, amelyek lehetővé teszik az alkalmazás Kubernetes clusteren való üzembe helyezését. Most megmutatom ezeket a fájlokat. A k8s mappában találhatók a Kubernetes resource definíciók. Először a namespace.yml fájl, amely egy saját namespace-t hoz létre php-todo-app néven, így izolálva az alkalmazás resource-ait. A mysql.yml fájl egy StatefulSet-et definiál a MySQL számára, amely PersistentVolumeClaim-et használ az adatok perzisztens tárolásához. Ez biztosítja, hogy az adatbázis újraindítás esetén is megőrzi az adatokat. A fájl tartalmaz egy ConfigMap-et is az init script-hez, amely létrehozza a todos táblát. Az app-deployment.yml fájl definiálja az alkalmazás Deployment-jét 2 replica-val, ami azt jelenti, hogy 2 pod fut párhuzamosan. Tartalmaz liveness és readiness probe-okat, amelyek monitorozzák a pod-ok állapotát. Az app-service.yml egy NodePort típusú Service-t hoz létre, amely külső hozzáférést biztosít az alkalmazáshoz. A deployment-hez szükség van egy Kubernetes cluster-re, például Minikube-ra lokális teszteléshez vagy egy cloud provider által menedzselt Kubernetes cluster-re production használatra. A Minikube telepítése egyszerű: le kell tölteni a minikube binárist, majd kubectl-t, és egyetlen paranccsal elindítható: minikube start. Ezután a kubectl apply parancsokkal telepíthetők a manifest fájlok. A projekt README és START fájlok részletes útmutatót tartalmaznak a Kubernetes deployment-hez is. Ez demonstrálja, hogy az alkalmazás teljes mértékben készen áll container orchestration platformon való futtatásra, ami lehetővé teszi az auto-scaling-et, self-healing-et és advanced networking feature-öket."

---

## 15. Összefoglalás

### Lépések:
- Nyisd meg újra az összes URL-t böngésző tab-okban
- Gyors áttekintés

### URL-ek:
```
http://localhost:8000/api
http://localhost:8000/api/health
http://localhost:8000/api/todos
http://localhost:8080 (PHPMyAdmin)
http://localhost:9090 (Prometheus)
http://localhost:4000 (Grafana)
https://github.com/TatLiliana/devops-todo-project/actions
https://hub.docker.com/r/lilianat28/php-todo-api
```

### Bemondó szöveg:
"Összefoglalva a bemutatót: elkezdtük a projekt GitHub repository-ból való klónozásával, egyetlen paranccsal elindítottuk az összes szükséges szolgáltatást Docker Compose segítségével. Bemutattam az API működését, létrehoztunk TODO-kat és teszteltük az endpoint-okat. Végigmentünk a teljes Git workflow-n: láttuk, hogy develop branch-re push esetén csak a CI pipeline fut, amely PHP 8.2-vel és 8.3-mal is teszteli az alkalmazást, valamint Docker build-et végez. Main branch-re push esetén pedig mind a CI, mind a CD pipeline elindul, amely automatikusan deploy-olja az új verziót a Docker Hub-ra. A Pull Request workflow-nál láttuk, hogy a CI a merge előtt fut, így védve a main branch minőségét. Bemutattam a PHPMyAdmin-t, ahol láthattuk az adatbázis struktúrát és SQL query-ket futtattunk. A Prometheus-ban real-time metrikákat kérdeztünk le az alkalmazásról, a Grafana-ban pedig dashboard-okat hoztunk létre ezekhez a metrikákhoz vizuális megjelenítéssel. Végül bemutattam a Kubernetes manifest fájlokat is, amelyek lehetővé teszik az alkalmazás production-ready container orchestration platformon való futtatását. Ez a projekt demonstrálja a teljes DevOps lifecycle-t: code, build, test, release, deploy és monitor fázisokat. Tartalmaz 7 különböző DevOps eszközt: GitHub Actions CI/CD-t, Docker Compose-t, Kubernetes-t, Prometheus-t, Grafana-t, PHPMyAdmin-t és MySQL-t. Az alkalmazás plain PHP-ban írt, MVC pattern-t követ, REST API best practice-eket alkalmaz, és teljes monitoring rendszerrel van felszerelve. Köszönöm a figyelmet!"

---

## 📝 Leállítási Parancsok (Videó Végén)

### Projekt leállítása:
```powershell
# Összes szolgáltatás leállítása
docker-compose down

# Vagy adatokkal együtt (teljes tisztítás)
docker-compose down -v
```

---

## 🎬 Videó Tippek

### Időbeosztás (ajánlott):
- **Bevezető**: 1-2 perc
- **Repository és környezet**: 2-3 perc
- **Docker Compose és API**: 3-4 perc
- **Git workflow (develop, main, PR)**: 5-7 perc
- **CI/CD pipeline-ok részletesen**: 4-5 perc
- **Docker Hub**: 1-2 perc
- **Lokális deploy update**: 2 perc
- **PHPMyAdmin**: 2-3 perc
- **Prometheus**: 3-4 perc
- **Grafana**: 3-4 perc
- **Kubernetes előkészítettség**: 2-3 perc
- **Összefoglalás**: 2 perc

**Teljes videó hossz: ~30-40 perc**

### Előkészítés:
1. Zárd be az összes felesleges alkalmazást
2. Tisztítsd meg a Desktop-ot
3. Állíts be nagyobb font size-t a terminálban
4. Készíts előre egy clean Git repository-t (commitolj mindent előtte)
5. Tesztelj mindent egyszer végig script nélkül
6. Legyen kéznél víz :)

### Rögzítés közben:
- Beszélj lassan és érthetően
- Ha elrontasz valamit, szüneteltess és vágd később
- Mutasd a kurzort, hogy a néző tudja követni
- Várj pár másodpercet minden parancs után, hogy látszódjon az eredmény
- Zoom-olj be, ha kell (Ctrl + görgő)

### Szerkesztés után:
- Vágd ki a hosszú várakozásokat (pl. Docker pull)
- Speed up-old a lassú részeket (1.5x)
- Add hozzá a feliratokat (ha szükséges)

---
