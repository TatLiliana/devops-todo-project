# API Példák és Tesztelés

## Alap Információk

**Base URL:** `http://localhost:8000`

**Content-Type:** `application/json`

**Válasz formátum:** JSON

---

## 1. API Root - Információk

### Request
```bash
GET /api
```

### cURL
```bash
curl http://localhost:8000/api
```

### PowerShell
```powershell
Invoke-RestMethod -Uri "http://localhost:8000/api" -Method Get
```

### Válasz
```json
{
  "message": "DevOps TODO API - Plain PHP",
  "version": "1.0.0",
  "endpoints": {
    "GET /api/health": "Health check",
    "GET /api/metrics": "Prometheus metrics",
    "GET /api/todos": "List all todos",
    "GET /api/todos/{id}": "Get single todo",
    "POST /api/todos": "Create new todo",
    "PUT /api/todos/{id}": "Update todo",
    "PATCH /api/todos/{id}/toggle": "Toggle todo completion",
    "DELETE /api/todos/{id}": "Delete todo"
  }
}
```

---

## 2. Health Check

### Request
```bash
GET /api/health
```

### cURL
```bash
curl http://localhost:8000/api/health
```

### PowerShell
```powershell
Invoke-RestMethod -Uri "http://localhost:8000/api/health" -Method Get
```

### Válasz
```json
{
  "status": "healthy",
  "timestamp": "2025-11-27 23:00:00",
  "database": "connected",
  "uptime_seconds": 3600,
  "php_version": "8.2.29",
  "memory_usage": {
    "current": 2097152,
    "peak": 2097152
  }
}
```

---

## 3. Prometheus Metrics

### Request
```bash
GET /api/metrics
```

### cURL
```bash
curl http://localhost:8000/api/metrics
```

### PowerShell
```powershell
Invoke-RestMethod -Uri "http://localhost:8000/api/metrics" -Method Get
```

### Válasz
```
# HELP total_todos Total number of todos
# TYPE total_todos gauge
total_todos 5

# HELP active_todos Number of active (incomplete) todos
# TYPE active_todos gauge
active_todos 4

# HELP completed_todos Number of completed todos
# TYPE completed_todos gauge
completed_todos 1
```

---

## 4. List All TODOs

### Request
```bash
GET /api/todos
```

### cURL
```bash
# Összes TODO
curl http://localhost:8000/api/todos

# Csak befejezettek
curl "http://localhost:8000/api/todos?completed=true"

# Csak aktívak
curl "http://localhost:8000/api/todos?completed=false"

# Magas prioritásúak
curl "http://localhost:8000/api/todos?priority=high"

# Kombinált filter
curl "http://localhost:8000/api/todos?completed=false&priority=high"
```

### PowerShell
```powershell
# Összes TODO
Invoke-RestMethod -Uri "http://localhost:8000/api/todos" -Method Get

# Csak befejezettek
Invoke-RestMethod -Uri "http://localhost:8000/api/todos?completed=true" -Method Get

# Magas prioritásúak
Invoke-RestMethod -Uri "http://localhost:8000/api/todos?priority=high" -Method Get
```

### Válasz
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Setup Docker environment",
      "description": "Configure Docker containers for the application",
      "completed": true,
      "priority": "high",
      "due_date": "2025-11-20 18:00:00",
      "created_at": "2025-11-27 10:00:00",
      "updated_at": "2025-11-27 10:00:00"
    },
    {
      "id": 2,
      "title": "Implement CI/CD pipeline",
      "description": "Create GitHub Actions workflows",
      "completed": false,
      "priority": "high",
      "due_date": "2025-11-25 23:59:59",
      "created_at": "2025-11-27 10:00:00",
      "updated_at": "2025-11-27 10:00:00"
    }
  ],
  "count": 2
}
```

---

## 5. Get Single TODO

### Request
```bash
GET /api/todos/{id}
```

### cURL
```bash
curl http://localhost:8000/api/todos/1
```

### PowerShell
```powershell
Invoke-RestMethod -Uri "http://localhost:8000/api/todos/1" -Method Get
```

### Válasz (Success)
```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": "Setup Docker environment",
    "description": "Configure Docker containers for the application",
    "completed": true,
    "priority": "high",
    "due_date": "2025-11-20 18:00:00",
    "created_at": "2025-11-27 10:00:00",
    "updated_at": "2025-11-27 10:00:00"
  }
}
```

### Válasz (Not Found)
```json
{
  "success": false,
  "error": "Todo not found"
}
```

---

## 6. Create TODO

### Request
```bash
POST /api/todos
Content-Type: application/json
```

### cURL
```bash
# Minimális (csak title kötelező)
curl -X POST http://localhost:8000/api/todos \
  -H "Content-Type: application/json" \
  -d '{"title":"New TODO"}'

# Teljes adatokkal
curl -X POST http://localhost:8000/api/todos \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Projectmunka befejezése",
    "description": "README és dokumentáció elkészítése",
    "priority": "high",
    "due_date": "2025-12-01 23:59:59",
    "completed": false
  }'
```

### PowerShell
```powershell
# Minimális
$body = @{
    title = "New TODO"
} | ConvertTo-Json

Invoke-RestMethod -Uri "http://localhost:8000/api/todos" `
  -Method Post `
  -ContentType "application/json; charset=utf-8" `
  -Body ([System.Text.Encoding]::UTF8.GetBytes($body))

# Teljes
$body = @{
    title = "Projectmunka befejezése"
    description = "README és dokumentáció elkészítése"
    priority = "high"
    due_date = "2025-12-01 23:59:59"
    completed = $true
} | ConvertTo-Json

Invoke-RestMethod -Uri "http://localhost:8000/api/todos" `
  -Method Post `
  -ContentType "application/json; charset=utf-8" `
  -Body ([System.Text.Encoding]::UTF8.GetBytes($body))
```

### Request Body
```json
{
  "title": "TODO címe (kötelező, max 200 karakter)",
  "description": "Részletes leírás (opcionális)",
  "priority": "low|medium|high (opcionális, default: medium)",
  "due_date": "YYYY-MM-DD HH:MM:SS (opcionális)",
  "completed": false (opcionális, default: false)
}
```

### Válasz (Success - 201 Created)
```json
{
  "success": true,
  "message": "Todo created successfully",
  "data": {
    "id": 6,
    "title": "Projectmunka befejezése",
    "description": "README és dokumentáció elkészítése",
    "completed": false,
    "priority": "high",
    "due_date": "2025-12-01 23:59:59",
    "created_at": "2025-11-27 23:00:00",
    "updated_at": "2025-11-27 23:00:00"
  }
}
```

### Válasz (Error - 400 Bad Request)
```json
{
  "success": false,
  "error": "Title is required"
}
```

---

## 7. Update TODO

### Request
```bash
PUT /api/todos/{id}
Content-Type: application/json
```

### cURL
```bash
# Egy mező frissítése
curl -X PUT http://localhost:8000/api/todos/1 \
  -H "Content-Type: application/json" \
  -d '{"title":"Updated title"}'

# Több mező frissítése
curl -X PUT http://localhost:8000/api/todos/1 \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Frissített cím",
    "description": "Frissített leírás",
    "priority": "medium",
    "completed": true
  }'
```

### PowerShell
```powershell
# Egy mező
$body = @{
    title = "Updated title"
} | ConvertTo-Json

Invoke-RestMethod -Uri "http://localhost:8000/api/todos/1" `
  -Method Put `
  -ContentType "application/json" `
  -Body $body

# Több mező
$body = @{
    title = "Frissített cím"
    description = "Frissített leírás"
    priority = "medium"
    completed = $true
} | ConvertTo-Json

Invoke-RestMethod -Uri "http://localhost:8000/api/todos/1" `
  -Method Put `
  -ContentType "application/json" `
  -Body $body
```

### Request Body (minden mező opcionális)
```json
{
  "title": "Új cím",
  "description": "Új leírás",
  "priority": "high",
  "due_date": "2025-12-31 23:59:59",
  "completed": true
}
```

### Válasz (Success - 200 OK)
```json
{
  "success": true,
  "message": "Todo updated successfully",
  "data": {
    "id": 1,
    "title": "Frissített cím",
    "description": "Frissített leírás",
    "completed": true,
    "priority": "medium",
    "due_date": "2025-11-20 18:00:00",
    "created_at": "2025-11-27 10:00:00",
    "updated_at": "2025-11-27 23:05:00"
  }
}
```

---

## 8. Toggle TODO Completion

### Request
```bash
PATCH /api/todos/{id}/toggle
```

### cURL
```bash
# Befejezettség váltása (completed: false → true vagy true → false)
curl -X PATCH http://localhost:8000/api/todos/1/toggle
```

### PowerShell
```powershell
Invoke-RestMethod -Uri "http://localhost:8000/api/todos/1/toggle" -Method Patch
```

### Válasz
```json
{
  "success": true,
  "message": "Todo completion toggled",
  "data": {
    "id": 1,
    "title": "Setup Docker environment",
    "description": "Configure Docker containers",
    "completed": false,
    "priority": "high",
    "due_date": "2025-11-20 18:00:00",
    "created_at": "2025-11-27 10:00:00",
    "updated_at": "2025-11-27 23:10:00"
  }
}
```

---

## 9. Delete TODO

### Request
```bash
DELETE /api/todos/{id}
```

### cURL
```bash
curl -X DELETE http://localhost:8000/api/todos/1
```

### PowerShell
```powershell
Invoke-RestMethod -Uri "http://localhost:8000/api/todos/1" -Method Delete
```

### Válasz (Success - 200 OK)
```json
{
  "success": true,
  "message": "Todo deleted successfully"
}
```

### Válasz (Not Found - 404)
```json
{
  "success": false,
  "error": "Todo not found"
}
```

---

## 10. Teljes Workflow Példa

### Komplett TODO életciklus

```bash
# 1. Lista előtte
curl http://localhost:8000/api/todos

# 2. Új TODO létrehozása
curl -X POST http://localhost:8000/api/todos \
  -H "Content-Type: application/json" \
  -d '{
    "title": "DevOps projekt védése",
    "description": "Prezentáció készítése és gyakorlás",
    "priority": "high",
    "due_date": "2025-12-10 14:00:00"
  }'

# Response: {"success":true,"data":{"id":7,...}}

# 3. TODO lekérése
curl http://localhost:8000/api/todos/7

# 4. TODO módosítása
curl -X PUT http://localhost:8000/api/todos/7 \
  -H "Content-Type: application/json" \
  -d '{"description": "Prezentáció kész, csak gyakorlás maradt"}'

# 5. TODO befejezése
curl -X PATCH http://localhost:8000/api/todos/7/toggle

# 6. Ellenőrzés
curl http://localhost:8000/api/todos/7

# 7. Törlés
curl -X DELETE http://localhost:8000/api/todos/7

# 8. Lista utána
curl http://localhost:8000/api/todos
```

---

## 11. Postman Collection

Ha Postman-t használsz, importálhatod ezt a collection-t:

```json
{
  "info": {
    "name": "DevOps TODO API",
    "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
  },
  "item": [
    {
      "name": "Get API Info",
      "request": {
        "method": "GET",
        "url": "http://localhost:8000/api"
      }
    },
    {
      "name": "Health Check",
      "request": {
        "method": "GET",
        "url": "http://localhost:8000/api/health"
      }
    },
    {
      "name": "List All TODOs",
      "request": {
        "method": "GET",
        "url": "http://localhost:8000/api/todos"
      }
    },
    {
      "name": "Get TODO by ID",
      "request": {
        "method": "GET",
        "url": "http://localhost:8000/api/todos/1"
      }
    },
    {
      "name": "Create TODO",
      "request": {
        "method": "POST",
        "header": [
          {
            "key": "Content-Type",
            "value": "application/json"
          }
        ],
        "body": {
          "mode": "raw",
          "raw": "{\n  \"title\": \"New TODO\",\n  \"description\": \"Description here\",\n  \"priority\": \"high\"\n}"
        },
        "url": "http://localhost:8000/api/todos"
      }
    },
    {
      "name": "Update TODO",
      "request": {
        "method": "PUT",
        "header": [
          {
            "key": "Content-Type",
            "value": "application/json"
          }
        ],
        "body": {
          "mode": "raw",
          "raw": "{\n  \"title\": \"Updated title\",\n  \"completed\": true\n}"
        },
        "url": "http://localhost:8000/api/todos/1"
      }
    },
    {
      "name": "Toggle TODO",
      "request": {
        "method": "PATCH",
        "url": "http://localhost:8000/api/todos/1/toggle"
      }
    },
    {
      "name": "Delete TODO",
      "request": {
        "method": "DELETE",
        "url": "http://localhost:8000/api/todos/1"
      }
    }
  ]
}
```

---

## 12. PowerShell Script - Teljes Teszt

Mentsd el `test-api.ps1` néven:

```powershell
# API Teszt Script
$baseUrl = "http://localhost:8000"

Write-Host "=== DevOps TODO API Teszt ===" -ForegroundColor Cyan

# 1. API Info
Write-Host "`n1. API Info:" -ForegroundColor Yellow
Invoke-RestMethod -Uri "$baseUrl/api" | ConvertTo-Json

# 2. Health Check
Write-Host "`n2. Health Check:" -ForegroundColor Yellow
Invoke-RestMethod -Uri "$baseUrl/api/health" | ConvertTo-Json

# 3. List TODOs
Write-Host "`n3. Osszes TODO:" -ForegroundColor Yellow
$todos = Invoke-RestMethod -Uri "$baseUrl/api/todos"
Write-Host "Count: $($todos.count)"

# 4. Create TODO
Write-Host "`n4. Uj TODO letrehozasa:" -ForegroundColor Yellow
$newTodo = @{
    title = "PowerShell teszt TODO"
    description = "Letrehozva PowerShell scriptbol"
    priority = "medium"
} | ConvertTo-Json

$created = Invoke-RestMethod -Uri "$baseUrl/api/todos" `
  -Method Post `
  -ContentType "application/json" `
  -Body $newTodo

Write-Host "Letrehozott TODO ID: $($created.data.id)"

# 5. Update TODO
Write-Host "`n5. TODO frissitese:" -ForegroundColor Yellow
$update = @{
    title = "Frissitett cim PowerShellbol"
} | ConvertTo-Json

Invoke-RestMethod -Uri "$baseUrl/api/todos/$($created.data.id)" `
  -Method Put `
  -ContentType "application/json" `
  -Body $update

# 6. Toggle
Write-Host "`n6. TODO befejezese:" -ForegroundColor Yellow
Invoke-RestMethod -Uri "$baseUrl/api/todos/$($created.data.id)/toggle" -Method Patch

# 7. Delete
Write-Host "`n7. TODO torlese:" -ForegroundColor Yellow
Invoke-RestMethod -Uri "$baseUrl/api/todos/$($created.data.id)" -Method Delete

Write-Host "`n=== Teszt befejezve ===" -ForegroundColor Green
```

Futtatás:
```powershell
.\test-api.ps1
```

---

## 13. Bash Script - Teljes Teszt

Mentsd el `test-api.sh` néven:

```bash
#!/bin/bash

BASE_URL="http://localhost:8000"

echo "=== DevOps TODO API Teszt ==="

# 1. API Info
echo -e "\n1. API Info:"
curl -s $BASE_URL/api | jq .

# 2. Health Check
echo -e "\n2. Health Check:"
curl -s $BASE_URL/api/health | jq .

# 3. List TODOs
echo -e "\n3. Összes TODO:"
curl -s $BASE_URL/api/todos | jq '.count'

# 4. Create TODO
echo -e "\n4. Új TODO létrehozása:"
RESPONSE=$(curl -s -X POST $BASE_URL/api/todos \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Bash teszt TODO",
    "description": "Létrehozva bash scriptből",
    "priority": "high"
  }')

TODO_ID=$(echo $RESPONSE | jq -r '.data.id')
echo "Létrehozott TODO ID: $TODO_ID"

# 5. Update TODO
echo -e "\n5. TODO frissítése:"
curl -s -X PUT $BASE_URL/api/todos/$TODO_ID \
  -H "Content-Type: application/json" \
  -d '{"title": "Frissített cím bashből"}' | jq .

# 6. Toggle
echo -e "\n6. TODO befejezése:"
curl -s -X PATCH $BASE_URL/api/todos/$TODO_ID/toggle | jq .

# 7. Delete
echo -e "\n7. TODO törlése:"
curl -s -X DELETE $BASE_URL/api/todos/$TODO_ID | jq .

echo -e "\n=== Teszt befejezve ==="
```

Futtatás:
```bash
chmod +x test-api.sh
./test-api.sh
```

---

## 14. HTTP Status Codes

| Code | Jelentés | Mikor |
|------|----------|-------|
| 200 | OK | Sikeres GET, PUT, PATCH, DELETE |
| 201 | Created | Sikeres POST (új TODO) |
| 400 | Bad Request | Validációs hiba (pl. title hiányzik) |
| 404 | Not Found | TODO nem található |
| 500 | Internal Server Error | Szerver/adatbázis hiba |

---

## 15. Gyakori Hibák és Megoldások

### 400 - Title is required
```json
{"success":false,"error":"Title is required"}
```
**Megoldás:** Add meg a `title` mezőt a request body-ban.

### 404 - Todo not found
```json
{"success":false,"error":"Todo not found"}
```
**Megoldás:** Ellenőrizd, hogy létezik-e a TODO az adott ID-val.

### 500 - Database error
```json
{"success":false,"error":"Database error: ..."}
```
**Megoldás:** Ellenőrizd, hogy a MySQL konténer fut-e: `docker-compose ps`

---

## 16. Quick Reference

```bash
# Gyors tesztelés
curl http://localhost:8000/api                          # Info
curl http://localhost:8000/api/health                   # Health
curl http://localhost:8000/api/todos                    # List
curl http://localhost:8000/api/todos/1                  # Get
curl -X POST http://localhost:8000/api/todos \
  -H "Content-Type: application/json" \
  -d '{"title":"Test"}'                                 # Create
curl -X PUT http://localhost:8000/api/todos/1 \
  -H "Content-Type: application/json" \
  -d '{"completed":true}'                               # Update
curl -X PATCH http://localhost:8000/api/todos/1/toggle  # Toggle
curl -X DELETE http://localhost:8000/api/todos/1        # Delete
```

---

**Dokumentáció készült:** 2025 November
**Projekt:** DevOps TODO API - Plain PHP
**GitHub:** https://github.com/TatLiliana/devops-todo-project
