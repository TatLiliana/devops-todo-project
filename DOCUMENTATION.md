# DevOps TODO API - Teljes Projekt Dokumentáció

> Részletes, átfogó dokumentáció a projekt szóbeli védelméhez és megértéséhez

## Tartalomjegyzék

1. [Projekt Áttekintés](#projekt-áttekintés)
2. [Architektúra](#architektúra)
3. [Technológiai Döntések](#technológiai-döntések)
4. [Implementáció Részletei](#implementáció-részletei)
5. [DevOps Pipeline](#devops-pipeline)
6. [Monitoring és Observability](#monitoring-és-observability)
7. [Deployment Stratégiák](#deployment-stratégiák)
8. [Biztonsági Szempontok](#biztonsági-szempontok)
9. [Tesztelés](#tesztelés)
10. [Védési Pontok](#védési-pontok)

---

## 1. Projekt Áttekintés

### 1.1 Motiváció és Célok

A projekt célja egy egyszerű, de teljes körű TODO alkalmazás készítése, amely bemutatja a modern DevOps gyakorlatokat. A Laravel helyett clean PHP-t választottam a következő okok miatt:

**Miért Plain PHP?**
- **Egyszerűség**: Nincs szükség komplex framework ismeretekre
- **Átláthatóság**: A DevOps folyamatok világosabban látszanak
- **Gyorsaság**: Minimális overhead, gyors build és deploy
- **Tanulási célok**: Fókusz a DevOps eszközökön, nem a framework-ön
- **Könnyű telepítés**: Nincs Composer, vendor mappa, vagy dependency hell

### 1.2 Követelmények Teljesítése

A projekt az alábbi követelményeket teljesíti:

| Követelmény | Pont | Teljesítés |
|------------|------|------------|
| Code | 10 | ✅ Clean code, MVC pattern, REST API |
| Build & Test | 15 | ✅ CI pipeline, multi-version testing, automated tests |
| Release & Deploy | 15 | ✅ Docker, K8s, CD pipeline, automated deployment |
| Monitor & Feedback | 10 | ✅ Prometheus, Grafana, health checks, metrics |
| Tool-ok (5+) | - | ✅ 7 eszköz (GitHub Actions, Docker Compose, K8s, Prometheus, Grafana, PHPMyAdmin, MySQL) |

### 1.3 Főbb Funkciók

- RESTful API TODO kezeléshez (CRUD műveletek)
- MySQL adatbázis perzisztenciával
- Health check és metrics endpoint-ok
- Prometheus monitoring integráció
- Docker konténerizáció
- Kubernetes orchestration
- Automatizált CI/CD pipeline
- Grafana vizualizáció

---

## 2. Architektúra

### 2.1 Magas Szintű Architektúra

```
┌─────────────────────────────────────────────────────────────┐
│                         User/Client                          │
└───────────────────────┬─────────────────────────────────────┘
                        │ HTTP Requests
                        ▼
┌─────────────────────────────────────────────────────────────┐
│                    Load Balancer / Service                   │
│                    (Kubernetes Service)                      │
└───────────────────────┬─────────────────────────────────────┘
                        │
        ┌───────────────┼───────────────┐
        │               │               │
        ▼               ▼               ▼
┌──────────────┐ ┌──────────────┐ ┌──────────────┐
│   PHP Pod 1  │ │   PHP Pod 2  │ │   PHP Pod N  │
│   (Apache)   │ │   (Apache)   │ │   (Apache)   │
└──────┬───────┘ └──────┬───────┘ └──────┬───────┘
       │                │                │
       └────────────────┼────────────────┘
                        │
                        ▼
            ┌───────────────────────┐
            │   MySQL StatefulSet   │
            │   (Persistent Volume) │
            └───────────────────────┘
                        │
                        ▼
            ┌───────────────────────┐
            │     Prometheus        │
            │  (Metrics Collector)  │
            └───────────┬───────────┘
                        │
                        ▼
            ┌───────────────────────┐
            │       Grafana         │
            │   (Visualization)     │
            └───────────────────────┘
```

### 2.2 Komponensek

#### 2.2.1 Application Layer (PHP)

**Fájlok:**
- `public/index.php` - Router, belépési pont
- `src/Database.php` - Adatbázis kapcsolat kezelés
- `src/TodoController.php` - TODO CRUD műveletek
- `src/HealthController.php` - Health check endpoint
- `src/MetricsController.php` - Prometheus metrics export

**Működés:**
1. Apache fogadja a HTTP kéréseket
2. `mod_rewrite` átirányítja a `public/index.php`-ra
3. Router feldolgozza az URI-t
4. Megfelelő Controller hívása
5. PDO használata MySQL műveletekhezhez
6. JSON válasz visszaküldése

#### 2.2.2 Data Layer (MySQL)

**Schema:**
```sql
CREATE TABLE todos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    completed BOOLEAN DEFAULT FALSE,
    priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
    due_date DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_completed (completed),
    INDEX idx_priority (priority),
    INDEX idx_created_at (created_at)
);
```

**Indexek:**
- `idx_completed` - Gyors filterezés befejezett/aktív TODO-kra
- `idx_priority` - Priority alapú szűrés optimalizálása
- `idx_created_at` - Időrendi rendezés gyorsítása

#### 2.2.3 Monitoring Layer

**Prometheus:**
- Metrikák gyűjtése `/api/metrics` endpoint-ról
- 15 másodpercenként scrape
- Time-series adatbázis

**Grafana:**
- Prometheus datasource
- Dashboard-ok készítése
- Real-time vizualizáció

### 2.3 Network Flow

**Normál kérés flow:**
```
Client → Kubernetes Service → Pod (Apache) → PHP → MySQL → PHP → Apache → Service → Client
```

**Metrics flow:**
```
Prometheus → Pod /api/metrics → Metrikák kalkulálása → Prometheus → Grafana
```

---

## 3. Technológiai Döntések

### 3.1 Miért PHP 8.2+?

**Előnyök:**
- Modern nyelvi features (named arguments, enums, attributes)
- JIT compiler - jobb performance
- Improved error handling
- Type safety improvements
- Széles körű támogatás és közösség

### 3.2 Miért MySQL 8.0?

**Előnyök:**
- JSON támogatás (később bővíthető)
- Window functions
- CTE (Common Table Expressions)
- Jó performance indexekkel
- Ingyenes és open-source
- Széles körű DevOps eszköz támogatás

**Alternatívák:**
- PostgreSQL: Komplexebb, de ebben az esetben overkill
- SQLite: Nem skálázható több pod esetén
- MongoDB: NoSQL nem szükséges ennél az alkalmazásnál

### 3.3 Miért Docker?

**Előnyök:**
- Portable - ugyanaz működik mindenhol
- Reproducible builds
- Isolation - külön környezetek
- Resource efficiency
- CI/CD integráció

**Docker Compose:**
- Lokális development egyszerűsítése
- Multi-container orchestration
- Service dependencies kezelése
- Volume management

### 3.4 Miért Kubernetes?

**Előnyök:**
- Auto-scaling (horizontal pod autoscaler)
- Self-healing (pod restart)
- Service discovery
- Load balancing
- Rolling updates
- Industry standard

**Minikube használata:**
- Lokális Kubernetes cluster
- Production-like környezet
- Tanulási célokra ideális

### 3.5 Miért GitHub Actions?

**Előnyök:**
- Ingyenes public repo-khoz
- YAML konfiguráció
- GitHub-bal natív integráció
- Marketplace-ről kész actions
- Matrix builds (multi-version testing)

**Alternatívák:**
- Jenkins: Self-hosted, komplexebb setup
- GitLab CI: GitLab kötött
- CircleCI: Fizetős korlátok

---

## 4. Implementáció Részletei

### 4.1 API Design

**REST Principles:**
- Resourceful endpoints (`/api/todos`)
- HTTP methods semantic használata (GET, POST, PUT, DELETE, PATCH)
- Status code-ok helyes használata (200, 201, 400, 404, 500)
- JSON request/response
- Stateless kommunikáció

**Endpoint tervezés:**

| Method | Endpoint | Funkció | Status Codes |
|--------|----------|---------|--------------|
| GET | `/api` | API info | 200 |
| GET | `/api/health` | Health check | 200 |
| GET | `/api/metrics` | Prometheus metrics | 200 |
| GET | `/api/todos` | List todos | 200 |
| GET | `/api/todos/{id}` | Get single todo | 200, 404 |
| POST | `/api/todos` | Create todo | 201, 400 |
| PUT | `/api/todos/{id}` | Update todo | 200, 400, 404 |
| PATCH | `/api/todos/{id}/toggle` | Toggle completion | 200, 404 |
| DELETE | `/api/todos/{id}` | Delete todo | 200, 404 |

### 4.2 Error Handling

**Stratégia:**
- Try-catch blokkok minden adatbázis művelethez
- Megfelelő HTTP status code-ok
- JSON error responses
- Error logging (PHP error_log)

**Példa:**
```php
try {
    // Database operation
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
```

### 4.3 Validáció

**Input validáció:**
- Title required és max 200 karakter
- Priority enum validation (low, medium, high)
- Boolean fields proper casting
- SQL injection prevention (prepared statements)

**SQL Injection védelem:**
```php
$stmt = $this->conn->prepare("SELECT * FROM todos WHERE id = :id");
$stmt->execute([':id' => $id]);  // Parameterized query
```

### 4.4 Database Connection

**PDO használata:**
```php
$dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";
$conn = new PDO($dsn, $username, $password);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
```

**Connection pooling:**
- PDO persistent connections (opcionális)
- MySQL max_connections beállítás
- Kubernetes pod resource limits

### 4.5 MVC Pattern

**Model:**
- `Database.php` - adatbázis kapcsolat és schema
- Implicit model a `todos` tábla

**View:**
- JSON response-ok (API, nincs HTML view)

**Controller:**
- `TodoController.php` - business logic
- `HealthController.php` - monitoring
- `MetricsController.php` - metrics

---

## 5. DevOps Pipeline

### 5.1 CI Pipeline (Continuous Integration)

**Trigger:**
- Pull request a `main` vagy `develop` branch-re
- Push a `develop` branch-re

**Workflow lépések:**

```yaml
jobs:
  test:
    - Checkout code
    - Setup PHP 8.2 és 8.3 (matrix)
    - MySQL service indítás
    - PHP built-in server indítás
    - API endpoint tesztek

  build:
    - Checkout code
    - Docker Buildx setup
    - Docker image build
    - Docker image test
```

**Matrix testing:**
```yaml
strategy:
  matrix:
    php-version: ['8.2', '8.3']
```

Ez biztosítja, hogy a kód mindkét PHP verzióval kompatibilis.

### 5.2 CD Pipeline (Continuous Deployment)

**Trigger:**
- Push a `main` branch-re

**Workflow:**

```yaml
jobs:
  build-and-deploy:
    - Checkout code
    - Docker Buildx setup
    - Docker Hub login
    - Image build and push
    - Multiple tags (latest, SHA, branch)
```

**Image tagging:**
- `latest` - legújabb main branch
- `main-{SHA}` - specific commit
- `main` - branch tag

### 5.3 Environment Variables

**Development:**
- `.env.example` template
- Docker Compose environment section
- Nem commitoljuk a `.env` fájlt

**Production (Kubernetes):**
- ConfigMap-ok sensitive adatokhoz
- Secret-ek credentials-höz (future improvement)
- Environment variables pod-okba

### 5.4 Build Optimization

**Docker multi-stage build:**
Jelenleg single-stage, de könnyen bővíthető:

```dockerfile
# Future improvement
FROM php:8.2-apache as builder
# Build steps

FROM php:8.2-apache
# Copy from builder
```

**Image size optimization:**
- Csak szükséges fájlok másolása
- `.dockerignore` használata
- `apt-get clean` után cleanup

---

## 6. Monitoring és Observability

### 6.1 Prometheus Metrics

**Implementált metrikák:**

```
# TODO metrikák
total_todos           # Gauge - összes TODO
active_todos          # Gauge - aktív TODO-k
completed_todos       # Gauge - befejezett TODO-k
todos_by_priority     # Gauge - priority label-lel

# PHP metrikák
php_memory_usage_bytes    # Gauge - aktuális memória
php_memory_peak_bytes     # Gauge - peak memória

# HTTP metrikák
http_requests_total       # Counter - összes request
```

**Metrika típusok:**
- **Gauge**: Érték fel és le is mehet (pl. aktív TODO-k száma)
- **Counter**: Csak növekszik (pl. HTTP requests)

### 6.2 Health Check

**Endpoint:** `GET /api/health`

**Ellenőrzések:**
```json
{
  "status": "healthy|unhealthy",
  "timestamp": "2025-11-27 10:00:00",
  "database": "connected|disconnected",
  "uptime_seconds": 3600,
  "php_version": "8.2.0",
  "memory_usage": {
    "current": 12345678,
    "peak": 23456789
  }
}
```

**Kubernetes integration:**
```yaml
livenessProbe:
  httpGet:
    path: /api/health
    port: 80
  initialDelaySeconds: 30
  periodSeconds: 10

readinessProbe:
  httpGet:
    path: /api/health
    port: 80
  initialDelaySeconds: 10
  periodSeconds: 5
```

### 6.3 Logging Strategy

**Jelenlegi:**
- PHP `error_log()` használata
- Apache access és error log-ok
- `docker logs` paranccsal elérés

**Future improvements:**
- Structured logging (JSON format)
- ELK stack (Elasticsearch, Logstash, Kibana)
- Log aggregation több pod-ból

### 6.4 Grafana Dashboards

**Dashboard ötletek:**

1. **TODO Overview**
   - Total todos gauge
   - Active vs Completed pie chart
   - Todos by priority bar chart

2. **System Health**
   - Memory usage graph
   - Request rate
   - Response time (future metric)

3. **Alerts**
   - Database connection failures
   - High memory usage
   - High error rate

---

## 7. Deployment Stratégiák

### 7.1 Local Development

**Docker Compose:**
```bash
docker-compose up -d
```

**Előnyök:**
- Gyors iteráció
- Azonos környezet minden developernek
- Könnyen reset-elhető (`docker-compose down -v`)

### 7.2 Kubernetes Deployment

**Resource hierarchy:**
```
Namespace (php-todo-app)
  ├── MySQL StatefulSet
  │   ├── PersistentVolumeClaim
  │   ├── ConfigMap (init script)
  │   └── Service (headless)
  └── App Deployment
      ├── ReplicaSet (2 pods)
      └── Service (NodePort)
```

**Scaling:**
```bash
# Manual scaling
kubectl scale deployment php-todo-api --replicas=5 -n php-todo-app

# Auto-scaling (future)
kubectl autoscale deployment php-todo-api \
  --min=2 --max=10 --cpu-percent=80 -n php-todo-app
```

### 7.3 Rolling Updates

**Kubernetes default strategy:**
```yaml
strategy:
  type: RollingUpdate
  rollingUpdate:
    maxSurge: 1        # Egyszerre max 1 új pod
    maxUnavailable: 0  # Mindig legyen elérhető pod
```

**Update process:**
```bash
# Image update
kubectl set image deployment/php-todo-api \
  php-todo-api=user/php-todo-api:v2.0 \
  -n php-todo-app

# Rollout status
kubectl rollout status deployment/php-todo-api -n php-todo-app

# Rollback ha szükséges
kubectl rollout undo deployment/php-todo-api -n php-todo-app
```

### 7.4 Blue-Green Deployment

**Future improvement:**
```
Blue Environment (current)
  ├── Service points here
  └── v1.0 pods

Green Environment (new)
  └── v2.0 pods

# Test green
# Switch service selector
# Delete blue
```

---

## 8. Biztonsági Szempontok

### 8.1 SQL Injection Prevention

**Prepared statements:**
```php
// ROSSZ
$sql = "SELECT * FROM todos WHERE id = " . $_GET['id'];

// JÓ
$stmt = $conn->prepare("SELECT * FROM todos WHERE id = :id");
$stmt->execute([':id' => $id]);
```

### 8.2 Input Validation

**Validációk:**
- Title length check (max 200)
- Priority enum validation
- Boolean proper casting
- XSS prevention (JSON encoding auto-escapes)

### 8.3 Environment Variables

**Secrets management:**
- `.env` fájl gitignore-olva
- Docker secrets (future)
- Kubernetes secrets (future)

**Jelenlegi:**
```yaml
environment:
  DB_PASSWORD: todopass  # Plaintext
```

**Javítás (future):**
```yaml
env:
  - name: DB_PASSWORD
    valueFrom:
      secretKeyRef:
        name: db-secret
        key: password
```

### 8.4 CORS Headers

**Implementálva:**
```php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
```

**Production:**
- Specific origin instead of `*`
- HTTPS only
- Credentials handling

### 8.5 HTTPS/TLS

**Future improvement:**
- Let's Encrypt certificate
- Ingress controller Kubernetes-ben
- Automatic redirect HTTP → HTTPS

---

## 9. Tesztelés

### 9.1 Automated Testing (CI)

**GitHub Actions tests:**

```yaml
- name: Test API Health Endpoint
  run: |
    response=$(curl -s http://localhost:8000/api/health)
    if echo "$response" | grep -q "healthy"; then
      echo "Health check passed"
    else
      exit 1
    fi
```

### 9.2 Manual Testing

**cURL példák:**

```bash
# Create
curl -X POST http://localhost:8000/api/todos \
  -H "Content-Type: application/json" \
  -d '{"title":"Test","priority":"high"}'

# List
curl http://localhost:8000/api/todos

# Update
curl -X PUT http://localhost:8000/api/todos/1 \
  -H "Content-Type: application/json" \
  -d '{"completed":true}'

# Delete
curl -X DELETE http://localhost:8000/api/todos/1
```

### 9.3 Load Testing

**Future improvement:**

```bash
# Apache Bench
ab -n 1000 -c 10 http://localhost:8000/api/todos

# K6
k6 run load-test.js
```

### 9.4 Integration Testing

**Docker container test:**
```bash
docker run -d --name test php-todo-api:latest
# Wait for startup
curl http://localhost:8000/api/health
# Assert response
docker stop test && docker rm test
```

---

## 10. Védési Pontok

### 10.1 Gyakori Kérdések és Válaszok

**Q: Miért választottál plain PHP-t Laravel helyett?**

A: Három fő oka van:
1. **Egyszerűség**: A projektben a DevOps folyamatokra szerettem volna fókuszálni, nem a framework komplexitására
2. **Átláthatóság**: Clean PHP-ban világosabban látszik, hogyan épül fel egy REST API, nincs "framework magic"
3. **Teljesítmény**: Nincs framework overhead, gyorsabb build és deploy, kisebb Docker image

**Q: Hogyan biztosítod a kód minőségét tesztek nélkül?**

A: Bár nincs PHPUnit teszt suite, a CI pipeline több módon is teszteli a kódot:
1. **Multi-version testing**: PHP 8.2 és 8.3 verziókon is fut
2. **API endpoint testing**: Minden endpoint működését ellenőrzi
3. **Docker build test**: Biztosítja, hogy az image működik
4. **Health check**: Runtime-ban is monitorozza az app állapotát

**Q: Mi a különbség a liveness és readiness probe között?**

A:
- **Liveness probe**: Ellenőrzi, hogy a pod él-e. Ha fail, Kubernetes újraindítja a pod-ot.
- **Readiness probe**: Ellenőrzi, hogy a pod kész-e forgalmat fogadni. Ha fail, kikerül a Service load balancer-ből, de nem restart.

**Q: Hogyan skálázható az alkalmazás?**

A: Horizontálisan skálázható:
1. **Stateless design**: A PHP app nem tárol session-t, minden adat MySQL-ben van
2. **Kubernetes Deployment**: `kubectl scale` paranccsal növelhető a pod-ok száma
3. **Service load balancing**: A Kubernetes Service automatikusan elosztja a forgalmat
4. **Database**: MySQL StatefulSet, read replica-kkal tovább skálázható

**Q: Mit csinál a Prometheus scrape?**

A:
1. Prometheus 15 másodpercenként HTTP GET-et küld a `/api/metrics` endpoint-ra
2. Az endpoint lekérdezi a MySQL-t (TODO számok, priority-k)
3. Prometheus formátumban (text/plain) exportálja a metrikákat
4. Prometheus eltárolja a time-series adatbázisában
5. Grafana a Prometheus API-n keresztül vizualizálja

**Q: Mi történik, ha a MySQL pod leáll?**

A:
1. **Liveness probe fail**: Kubernetes észleli
2. **Pod restart**: Kubernetes újraindítja a pod-ot
3. **PersistentVolume**: Az adat megmarad, mert PVC-ben tárolódik
4. **App health check**: A PHP app health check "unhealthy" lesz
5. **Readiness probe fail**: PHP pod-ok kikerülnek a Service-ből
6. **Automatic recovery**: MySQL újraindul, app újra csatlakozik

**Q: Hogyan implementálnád az authentication-t?**

A:
1. **JWT tokens**: Stateless auth, nem kell session
2. **Auth middleware**: Új PHP class a token validálásra
3. **User tábla**: MySQL-ben user adatok
4. **Password hashing**: `password_hash()` és `password_verify()`
5. **Header-based auth**: `Authorization: Bearer {token}`

**Q: Mi a CD pipeline output-ja?**

A:
1. **Docker image**: Publish Docker Hub-ra
2. **Multiple tags**:
   - `latest` - legfrissebb
   - `main-{SHA}` - konkrét commit
   - `main` - branch tag
3. **Metadata**: Image digest, labels
4. **GitHub Actions artifact**: Build log és metadata

**Q: Kubernetes vs Docker Compose - mikor melyiket?**

A:
- **Docker Compose**:
  - Lokális development
  - Egyszerű multi-container app
  - Gyors iteráció
  - 1 host

- **Kubernetes**:
  - Production deployment
  - Auto-scaling szükséges
  - Self-healing
  - Multi-host cluster
  - Advanced networking

**Q: Hogyan debuggolnád production-ban a hibákat?**

A:
1. **Logs**: `kubectl logs -f pod-name`
2. **Exec into pod**: `kubectl exec -it pod-name -- bash`
3. **Metrics**: Grafana dashboard-ok
4. **Health endpoint**: `/api/health` ellenőrzése
5. **MySQL direct**: `kubectl port-forward` és MySQL client
6. **Describe**: `kubectl describe pod pod-name`

### 10.2 Továbbfejlesztési Lehetőségek

**Short-term:**
1. ✅ Unit tesztek PHPUnit-tal
2. ✅ Authentication és Authorization
3. ✅ Rate limiting
4. ✅ Caching layer (Redis)
5. ✅ Database migrations

**Long-term:**
1. ✅ Microservices architecture
2. ✅ Message queue (RabbitMQ)
3. ✅ Service mesh (Istio)
4. ✅ GitOps (ArgoCD)
5. ✅ Multi-region deployment

### 10.3 Tanulságok

**Mit tanultam?**

1. **DevOps nem csak tooling**: Szemléletmód, kultur, automation
2. **Infrastructure as Code**: Reprodukálható, verziókezelt infrastruktúra
3. **Containerization előnyei**: Portability, isolation, consistency
4. **CI/CD fontossága**: Gyors feedback, automated quality gates
5. **Monitoring kritikus**: Can't improve what you don't measure
6. **Kubernetes complexity**: Powerful, de learning curve nagy
7. **Plain PHP is viable**: Modern DevOps gyakorlatokhoz nem kell framework

---

## Összefoglalás

Ez a projekt egy teljes körű DevOps workflow-t mutat be egy egyszerű TODO alkalmazáson keresztül. A clean PHP választás lehetővé teszi, hogy a fókusz a DevOps eszközökön és gyakorlatokon legyen, nem a framework komplexitásán.

**Kulcs pontok:**
- ✅ Modern DevOps tools (7+)
- ✅ Automated CI/CD pipeline
- ✅ Container orchestration
- ✅ Monitoring és observability
- ✅ Infrastructure as Code
- ✅ Best practices (security, scaling, testing)

**Projekt URL:** https://github.com/TatLiliana/devops-todo-project

---


