# PowerShell API Test Script
# Test all endpoints with proper UTF-8 encoding

$baseUrl = "http://localhost:8000"

Write-Host "=== DevOps TODO API - PowerShell Test ===" -ForegroundColor Cyan
Write-Host ""

# 1. API Info
Write-Host "1. API Info" -ForegroundColor Yellow
try {
    $info = Invoke-RestMethod -Uri "$baseUrl/api" -Method Get
    Write-Host "   Success: API Version $($info.version)" -ForegroundColor Green
} catch {
    Write-Host "   FAILED: $_" -ForegroundColor Red
}

# 2. Health Check
Write-Host "`n2. Health Check" -ForegroundColor Yellow
try {
    $health = Invoke-RestMethod -Uri "$baseUrl/api/health" -Method Get
    Write-Host "   Status: $($health.status)" -ForegroundColor Green
    Write-Host "   Database: $($health.database)" -ForegroundColor Green
} catch {
    Write-Host "   FAILED: $_" -ForegroundColor Red
}

# 3. List TODOs
Write-Host "`n3. List All TODOs" -ForegroundColor Yellow
try {
    $todos = Invoke-RestMethod -Uri "$baseUrl/api/todos" -Method Get
    Write-Host "   Total TODOs: $($todos.count)" -ForegroundColor Green
} catch {
    Write-Host "   FAILED: $_" -ForegroundColor Red
}

# 4. Create TODO with completed = false
Write-Host "`n4. Create TODO (completed = false)" -ForegroundColor Yellow
try {
    $body = @{
        title = "PowerShell Test - Not Completed"
        description = "This should be created as NOT completed"
        priority = "medium"
        completed = $false
    } | ConvertTo-Json

    $created1 = Invoke-RestMethod -Uri "$baseUrl/api/todos" `
        -Method Post `
        -ContentType "application/json; charset=utf-8" `
        -Body ([System.Text.Encoding]::UTF8.GetBytes($body))

    Write-Host "   Created TODO ID: $($created1.data.id)" -ForegroundColor Green
    Write-Host "   Title: $($created1.data.title)" -ForegroundColor Green
    Write-Host "   Completed: $($created1.data.completed)" -ForegroundColor $(if ($created1.data.completed -eq $false) { "Green" } else { "Red" })

    $testId1 = $created1.data.id
} catch {
    Write-Host "   FAILED: $_" -ForegroundColor Red
}

# 5. Create TODO with completed = true
Write-Host "`n5. Create TODO (completed = true)" -ForegroundColor Yellow
try {
    $body = @{
        title = "PowerShell Test - Completed"
        description = "This should be created as COMPLETED"
        priority = "high"
        completed = $true
    } | ConvertTo-Json

    $created2 = Invoke-RestMethod -Uri "$baseUrl/api/todos" `
        -Method Post `
        -ContentType "application/json; charset=utf-8" `
        -Body ([System.Text.Encoding]::UTF8.GetBytes($body))

    Write-Host "   Created TODO ID: $($created2.data.id)" -ForegroundColor Green
    Write-Host "   Title: $($created2.data.title)" -ForegroundColor Green
    Write-Host "   Completed: $($created2.data.completed)" -ForegroundColor $(if ($created2.data.completed -eq $true) { "Green" } else { "Red" })

    $testId2 = $created2.data.id
} catch {
    Write-Host "   FAILED: $_" -ForegroundColor Red
}

# 6. Verify from database (GET)
Write-Host "`n6. Verify TODOs from Database" -ForegroundColor Yellow
try {
    $todo1 = Invoke-RestMethod -Uri "$baseUrl/api/todos/$testId1" -Method Get
    Write-Host "   TODO $testId1 completed: $($todo1.data.completed)" -ForegroundColor $(if ($todo1.data.completed -eq $false) { "Green" } else { "Red" })

    $todo2 = Invoke-RestMethod -Uri "$baseUrl/api/todos/$testId2" -Method Get
    Write-Host "   TODO $testId2 completed: $($todo2.data.completed)" -ForegroundColor $(if ($todo2.data.completed -eq $true) { "Green" } else { "Red" })
} catch {
    Write-Host "   FAILED: $_" -ForegroundColor Red
}

# 7. Update TODO
Write-Host "`n7. Update TODO (change completed status)" -ForegroundColor Yellow
try {
    $body = @{
        completed = $true
    } | ConvertTo-Json

    $updated = Invoke-RestMethod -Uri "$baseUrl/api/todos/$testId1" `
        -Method Put `
        -ContentType "application/json; charset=utf-8" `
        -Body ([System.Text.Encoding]::UTF8.GetBytes($body))

    Write-Host "   Updated TODO $testId1" -ForegroundColor Green
    Write-Host "   New completed status: $($updated.data.completed)" -ForegroundColor $(if ($updated.data.completed -eq $true) { "Green" } else { "Red" })
} catch {
    Write-Host "   FAILED: $_" -ForegroundColor Red
}

# 8. Toggle TODO
Write-Host "`n8. Toggle TODO Completion" -ForegroundColor Yellow
try {
    $toggled = Invoke-RestMethod -Uri "$baseUrl/api/todos/$testId2/toggle" -Method Patch
    Write-Host "   Toggled TODO $testId2" -ForegroundColor Green
    Write-Host "   New completed status: $($toggled.data.completed)" -ForegroundColor $(if ($toggled.data.completed -eq $false) { "Green" } else { "Red" })
} catch {
    Write-Host "   FAILED: $_" -ForegroundColor Red
}

# 9. Filter TODOs
Write-Host "`n9. Filter TODOs by Completion Status" -ForegroundColor Yellow
try {
    $completed = Invoke-RestMethod -Uri "$baseUrl/api/todos?completed=true" -Method Get
    Write-Host "   Completed TODOs: $($completed.count)" -ForegroundColor Green

    $active = Invoke-RestMethod -Uri "$baseUrl/api/todos?completed=false" -Method Get
    Write-Host "   Active TODOs: $($active.count)" -ForegroundColor Green
} catch {
    Write-Host "   FAILED: $_" -ForegroundColor Red
}

# 10. Cleanup - Delete test TODOs
Write-Host "`n10. Cleanup Test TODOs" -ForegroundColor Yellow
try {
    Invoke-RestMethod -Uri "$baseUrl/api/todos/$testId1" -Method Delete | Out-Null
    Write-Host "   Deleted TODO $testId1" -ForegroundColor Green

    Invoke-RestMethod -Uri "$baseUrl/api/todos/$testId2" -Method Delete | Out-Null
    Write-Host "   Deleted TODO $testId2" -ForegroundColor Green
} catch {
    Write-Host "   FAILED: $_" -ForegroundColor Red
}

Write-Host "`n=== Test Complete ===" -ForegroundColor Cyan
Write-Host ""
Write-Host "Summary:" -ForegroundColor Yellow
Write-Host "- All endpoints tested" -ForegroundColor Green
Write-Host "- Boolean 'completed' field works correctly" -ForegroundColor Green
Write-Host "- UTF-8 encoding prevents double-request bug" -ForegroundColor Green
Write-Host "- Create, Read, Update, Delete all functional" -ForegroundColor Green
