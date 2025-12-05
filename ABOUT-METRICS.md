# Prometheus Metrikák - Részletes Dokumentáció

> Hogyan működnek a metrikák? Hol vannak deklarálva? Hogyan kerülnek be Prometheus-ba?

---

## 📊 Áttekintés

Ez a projekt **valós idejű metrikákat** gyűjt a TODO alkalmazásból Prometheus formátumban. A metrikák az adatbázisból jönnek, és 15 másodpercenként frissülnek.

**Metrika endpoint:** http://localhost:8000/api/metrics

---

## 🔍 Metrikák Listája

| Metrika Név | Típus | Leírás | Példa Érték |
|-------------|-------|--------|-------------|
| `total_todos` | gauge | Összes TODO száma | `5` |
| `active_todos` | gauge | Aktív (nem befejezett) TODO-k | `3` |
| `completed_todos` | gauge | Befejezett TODO-k | `2` |
| `todos_by_priority` | gauge | TODO-k prioritás szerint (címkékkel) | `{priority="high"} 2` |
| `php_memory_usage_bytes` | gauge | Aktuális PHP memória használat | `2097152` |
| `php_memory_peak_bytes` | gauge | Maximum PHP memória használat | `4194304` |
| `http_requests_total` | counter | HTTP kérések száma | `1` |

---

## 📍 Forráskód - Hol vannak deklarálva?

### Fájl: `src/MetricsController.php`

**Összes metrika ebben az egy fájlban van!**

### 1. **`total_todos`** - Összes TODO száma

**Hol:** `src/MetricsController.php:17-18` és `33-35`

```php
// Adatbázis lekérdezés
$stmt = $this->conn->query("SELECT COUNT(*) as count FROM todos");
$totalTodos = $stmt->fetch()['count'];

// Prometheus metrika exportálás
echo "# HELP total_todos Total number of todos\n";
echo "# TYPE total_todos gauge\n";
echo "total_todos $totalTodos\n\n";
```

**SQL Query:**
```sql
SELECT COUNT(*) as count FROM todos
```

**Prometheus Output:**
```
# HELP total_todos Total number of todos
# TYPE total_todos gauge
total_todos 5
```

**Mit csinál:**
- Megszámolja az összes sort a `todos` táblában
- Gauge típusú metrika (aktuális érték)

---

### 2. **`active_todos`** - Aktív TODO-k

**Hol:** `src/MetricsController.php:21-22` és `37-39`

```php
// Adatbázis lekérdezés
$stmt = $this->conn->query("SELECT COUNT(*) as count FROM todos WHERE completed = 0");
$activeTodos = $stmt->fetch()['count'];

// Prometheus metrika exportálás
echo "# HELP active_todos Number of active (incomplete) todos\n";
echo "# TYPE active_todos gauge\n";
echo "active_todos $activeTodos\n\n";
```

**SQL Query:**
```sql
SELECT COUNT(*) as count FROM todos WHERE completed = 0
```

**Prometheus Output:**
```
# HELP active_todos Number of active (incomplete) todos
# TYPE active_todos gauge
active_todos 3
```

**Mit csinál:**
- Csak a `completed = 0` (false) TODO-kat számolja
- Ezek a még nem befejezett feladatok

---

### 3. **`completed_todos`** - Befejezett TODO-k

**Hol:** `src/MetricsController.php:25-26` és `41-43`

```php
// Adatbázis lekérdezés
$stmt = $this->conn->query("SELECT COUNT(*) as count FROM todos WHERE completed = 1");
$completedTodos = $stmt->fetch()['count'];

// Prometheus metrika exportálás
echo "# HELP completed_todos Number of completed todos\n";
echo "# TYPE completed_todos gauge\n";
echo "completed_todos $completedTodos\n\n";
```

**SQL Query:**
```sql
SELECT COUNT(*) as count FROM todos WHERE completed = 1
```

**Prometheus Output:**
```
# HELP completed_todos Number of completed todos
# TYPE completed_todos gauge
completed_todos 2
```

**Mit csinál:**
- Csak a `completed = 1` (true) TODO-kat számolja
- Ezek a már befejezett feladatok

---

### 4. **`todos_by_priority`** - TODO-k prioritás szerint

**Hol:** `src/MetricsController.php:29-30` és `45-52`

```php
// Adatbázis lekérdezés (GROUP BY prioritás)
$stmt = $this->conn->query("SELECT priority, COUNT(*) as count FROM todos GROUP BY priority");
$priorities = $stmt->fetchAll();

// Prometheus metrika exportálás (címkékkel)
echo "# HELP todos_by_priority Number of todos by priority level\n";
echo "# TYPE todos_by_priority gauge\n";
foreach ($priorities as $priority) {
    $label = $priority['priority'];
    $count = $priority['count'];
    echo "todos_by_priority{priority=\"$label\"} $count\n";
}
echo "\n";
```

**SQL Query:**
```sql
SELECT priority, COUNT(*) as count FROM todos GROUP BY priority
```

**SQL Eredmény példa:**
```
priority | count
---------|------
high     | 2
medium   | 1
low      | 2
```

**Prometheus Output:**
```
# HELP todos_by_priority Number of todos by priority level
# TYPE todos_by_priority gauge
todos_by_priority{priority="high"} 2
todos_by_priority{priority="medium"} 1
todos_by_priority{priority="low"} 2
```

**Mit csinál:**
- Csoportosítja a TODO-kat prioritás szerint (`GROUP BY priority`)
- **Prometheus labels használat:** Ugyanaz a metrika név, de különböző `priority` címkékkel
- Így Prometheus-ban szűrhetsz: `todos_by_priority{priority="high"}`

**Prometheus Label Szintaxis:**
```
metrika_név{címke="érték"} szám
```

---

### 5. **`php_memory_usage_bytes`** - PHP memória használat

**Hol:** `src/MetricsController.php:55-57`

```php
echo "# HELP php_memory_usage_bytes Current PHP memory usage in bytes\n";
echo "# TYPE php_memory_usage_bytes gauge\n";
echo "php_memory_usage_bytes " . memory_get_usage(true) . "\n\n";
```

**PHP Függvény:**
```php
memory_get_usage(true)  // true = rendszer által lefoglalt memória
```

**Prometheus Output:**
```
# HELP php_memory_usage_bytes Current PHP memory usage in bytes
# TYPE php_memory_usage_bytes gauge
php_memory_usage_bytes 2097152
```

**Mit csinál:**
- `memory_get_usage(true)` → Aktuális PHP script memória használata byte-ban
- 2097152 bytes = 2 MB

---

### 6. **`php_memory_peak_bytes`** - Maximum PHP memória

**Hol:** `src/MetricsController.php:59-61`

```php
echo "# HELP php_memory_peak_bytes Peak PHP memory usage in bytes\n";
echo "# TYPE php_memory_peak_bytes gauge\n";
echo "php_memory_peak_bytes " . memory_get_peak_usage(true) . "\n\n";
```

**PHP Függvény:**
```php
memory_get_peak_usage(true)  // Maximum memória amit a script elért
```

**Prometheus Output:**
```
# HELP php_memory_peak_bytes Peak PHP memory usage in bytes
# TYPE php_memory_peak_bytes gauge
php_memory_peak_bytes 4194304
```

**Mit csinál:**
- `memory_get_peak_usage(true)` → Script futás során elért maximum memória
- 4194304 bytes = 4 MB

---

### 7. **`http_requests_total`** - HTTP kérések

**Hol:** `src/MetricsController.php:64-66`

```php
echo "# HELP http_requests_total Total HTTP requests\n";
echo "# TYPE http_requests_total counter\n";
echo "http_requests_total 1\n\n";
```

**Prometheus Output:**
```
# HELP http_requests_total Total HTTP requests
# TYPE http_requests_total counter
http_requests_total 1
```

**Mit csinál:**
- Egyszerű számláló (counter típus)
- Jelenleg fix `1` érték minden metrika lekérésnél
- Bővíthető: session-based vagy Redis-based számlálóval

---

## 🔄 Hogyan kerülnek be Prometheus-ba?

### 1. **Prometheus Konfiguráció**

**Fájl:** `prometheus/prometheus.yml`

```yaml
scrape_configs:
  - job_name: 'php-todo-api'
    static_configs:
      - targets: ['app:80']
    metrics_path: '/api/metrics'
    scrape_interval: 15s
```

**Magyarázat:**
- `job_name`: Metrika forrás neve (PHP TODO API)
- `targets`: Hol található az alkalmazás (`app:80` = Docker container neve és port)
- `metrics_path`: API endpoint (`/api/metrics`)
- `scrape_interval`: **15 másodpercenként** lekérdezi a metrikákat

### 2. **Prometheus Scraping Folyamat**

```
┌─────────────────┐
│   Prometheus    │
│   (port 9090)   │
└────────┬────────┘
         │
         │ HTTP GET http://app:80/api/metrics
         │ Minden 15 másodpercben
         │
         ▼
┌─────────────────┐
│  PHP TODO API   │
│   (app:80)      │
└────────┬────────┘
         │
         │ public/index.php: /api/metrics route
         │
         ▼
┌─────────────────┐
│ MetricsController│
│  export() metódus│
└────────┬────────┘
         │
         │ 1. SQL query: SELECT COUNT(*) FROM todos
         │ 2. SQL query: SELECT COUNT(*) WHERE completed = 0
         │ 3. SQL query: SELECT COUNT(*) WHERE completed = 1
         │ 4. SQL query: GROUP BY priority
         │ 5. memory_get_usage()
         │
         ▼
┌─────────────────┐
│  MySQL Database │
│   (mysql:3306)  │
└─────────────────┘
         │
         │ Eredmények visszaadása
         │
         ▼
┌─────────────────┐
│ Prometheus Format│
│ plain text output│
└─────────────────┘
total_todos 5
active_todos 3
completed_todos 2
...
```

### 3. **Routing - API Endpoint**

**Fájl:** `public/index.php`

```php
// Metrics endpoint
if ($method === 'GET' && $path === '/api/metrics') {
    require_once __DIR__ . '/../src/MetricsController.php';
    $controller = new MetricsController($db);
    $controller->export();
    exit;
}
```

**Hogyan hívódik meg:**
1. Prometheus HTTP GET kérést küld: `http://app:80/api/metrics`
2. `public/index.php` routing: ha `/api/metrics` → MetricsController
3. `MetricsController->export()` metódus lefut
4. SQL query-k lekérdezik friss adatokat
5. Prometheus formátumban visszaadja (`text/plain`)
6. Prometheus tárolja az értékeket

---

## 📈 Metrikák Használata Prometheus-ban

### 1. Prometheus UI

**URL:** http://localhost:9090

**Példa query-k:**

```promql
# Összes TODO
total_todos

# Aktív TODO-k
active_todos

# Befejezett TODO-k aránya
completed_todos / total_todos * 100

# Magas prioritású TODO-k
todos_by_priority{priority="high"}

# Összes prioritás (high, medium, low)
todos_by_priority

# PHP memória MB-ban
php_memory_usage_bytes / 1024 / 1024

# TODO-k változása 5 perc alatt
rate(total_todos[5m])

# Befejezett TODO-k időbeli változása
delta(completed_todos[1h])
```

### 2. Grafana Dashboard

**URL:** http://localhost:4000

**Panel készítés:**
1. Kattints: **Create** → **Dashboard** → **Add visualization**
2. Válaszd: **Prometheus** data source
3. Query példák:
   - `active_todos` → Stat panel
   - `completed_todos` → Stat panel
   - `todos_by_priority` → Bar gauge (minden prioritás)
   - `php_memory_usage_bytes / 1024 / 1024` → Time series (MB-ban)

---

## 🧪 Metrikák Tesztelése

### 1. Böngészőben

Nyisd meg: http://localhost:8000/api/metrics

**Várt kimenet (plain text):**
```
# HELP total_todos Total number of todos
# TYPE total_todos gauge
total_todos 5

# HELP active_todos Number of active (incomplete) todos
# TYPE active_todos gauge
active_todos 3

# HELP completed_todos Number of completed todos
# TYPE completed_todos gauge
completed_todos 2

# HELP todos_by_priority Number of todos by priority level
# TYPE todos_by_priority gauge
todos_by_priority{priority="high"} 2
todos_by_priority{priority="medium"} 1
todos_by_priority{priority="low"} 2

# HELP php_memory_usage_bytes Current PHP memory usage in bytes
# TYPE php_memory_usage_bytes gauge
php_memory_usage_bytes 2097152

# HELP php_memory_peak_bytes Peak PHP memory usage in bytes
# TYPE php_memory_peak_bytes gauge
php_memory_peak_bytes 4194304

# HELP http_requests_total Total HTTP requests
# TYPE http_requests_total counter
http_requests_total 1
```

### 2. PowerShell

```powershell
Invoke-RestMethod -Uri "http://localhost:8000/api/metrics" -Method Get
```

### 3. cURL

```bash
curl http://localhost:8000/api/metrics
```

### 4. Dinamikus változások tesztelése

```powershell
# 1. Nézd meg az aktuális metrikákat
Invoke-RestMethod -Uri "http://localhost:8000/api/metrics"

# 2. Hozz létre egy új TODO-t
$body = @{
    title = "Teszt metrika"
    completed = $false
    priority = "high"
} | ConvertTo-Json

Invoke-RestMethod -Uri "http://localhost:8000/api/todos" `
    -Method Post `
    -ContentType "application/json; charset=utf-8" `
    -Body ([System.Text.Encoding]::UTF8.GetBytes($body))

# 3. Nézd meg újra a metrikákat (várj 1-2 másodpercet)
Invoke-RestMethod -Uri "http://localhost:8000/api/metrics"
```

**Várt változások:**
- `total_todos` nőtt 1-gyel
- `active_todos` nőtt 1-gyel
- `todos_by_priority{priority="high"}` nőtt 1-gyel

---

## 🔧 Metrika Típusok - Magyarázat

### Gauge (Mérőműszer)

**Jellemző:**
- Aktuális értéket mér (fel-le mehet)
- Példák: hőmérséklet, CPU használat, aktív felhasználók

**Projekt példák:**
- `total_todos` → növekedhet (új TODO) vagy csökkenhet (törlés)
- `active_todos` → változhat (toggle completion)
- `php_memory_usage_bytes` → dinamikusan változik

### Counter (Számláló)

**Jellemző:**
- Csak növekszik (soha nem csökken)
- Reset csak újraindításkor
- Példák: HTTP kérések száma, hibák száma

**Projekt példa:**
- `http_requests_total` → minden kérésnél nő

---

## 📊 Prometheus Labels - Részletesen

### Mi az a Label?

**Label = címke**, ami azonos metrikát különböző dimenziókban mutat.

**Példa projekt metrika:**
```
todos_by_priority{priority="high"} 2
todos_by_priority{priority="medium"} 1
todos_by_priority{priority="low"} 2
```

**Szintaxis:**
```
metrika_név{címke1="érték1", címke2="érték2"} szám
```

### Hogyan használd Prometheus-ban?

```promql
# Összes prioritás
todos_by_priority

# Csak high prioritás
todos_by_priority{priority="high"}

# High + medium prioritás összege
sum(todos_by_priority{priority=~"high|medium"})

# Legtöbb TODO-s prioritás
max(todos_by_priority)
```

### Forráskód - Label generálás

```php
// SQL: Csoportosítás prioritás szerint
$stmt = $this->conn->query("SELECT priority, COUNT(*) as count FROM todos GROUP BY priority");
$priorities = $stmt->fetchAll();

// Minden prioritáshoz külön label
foreach ($priorities as $priority) {
    $label = $priority['priority'];  // "high", "medium", "low"
    $count = $priority['count'];     // TODO-k száma
    echo "todos_by_priority{priority=\"$label\"} $count\n";
}
```

**Eredmény:**
```
todos_by_priority{priority="high"} 2     ← Label: priority="high"
todos_by_priority{priority="medium"} 1   ← Label: priority="medium"
todos_by_priority{priority="low"} 2      ← Label: priority="low"
```

---

## 🛠️ Bővítési Lehetőségek

### 1. További TODO metrikák

```php
// Lejárt határidejű TODO-k
$stmt = $this->conn->query("SELECT COUNT(*) FROM todos WHERE due_date < NOW() AND completed = 0");
$overdueTodos = $stmt->fetch()['count'];
echo "overdue_todos $overdueTodos\n";

// Mai nap létrehozott TODO-k
$stmt = $this->conn->query("SELECT COUNT(*) FROM todos WHERE DATE(created_at) = CURDATE()");
$todayTodos = $stmt->fetch()['count'];
echo "todos_created_today $todayTodos\n";
```

### 2. HTTP kérések pontos számolása

```php
// session-based számláló
session_start();
if (!isset($_SESSION['request_count'])) {
    $_SESSION['request_count'] = 0;
}
$_SESSION['request_count']++;
echo "http_requests_total {$_SESSION['request_count']}\n";
```

### 3. Response time metrika

```php
// index.php elején
$startTime = microtime(true);

// MetricsController.php-ben
global $startTime;
$responseTime = (microtime(true) - $startTime) * 1000; // ms
echo "http_request_duration_ms $responseTime\n";
```

---

## 📚 Összefoglaló

| Elem | Helye | Leírás |
|------|-------|--------|
| **Metrika deklarálás** | `src/MetricsController.php` | Összes metrika ebben az egy fájlban |
| **SQL query-k** | MetricsController.php:17-30 | Adatbázis lekérdezések |
| **Prometheus export** | MetricsController.php:33-66 | Plain text formátum generálás |
| **API endpoint** | `public/index.php` | `/api/metrics` routing |
| **Prometheus config** | `prometheus/prometheus.yml` | Scraping beállítások (15s) |
| **Tesztelés** | http://localhost:8000/api/metrics | Böngészőben nézd meg |

---


