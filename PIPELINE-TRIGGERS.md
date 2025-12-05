# GitHub Actions Pipeline Triggers

> Mikor fut a CI, mikor a CD, és mikor mindkettő?

---

## Trigger Összefoglaló Táblázat

| Esemény | Branch | CI futás? | CD futás? | Miért? |
|---------|--------|-----------|-----------|--------|
| **Push to `main`** | `main` | ✅ **IGEN** | ✅ **IGEN** | CI: tesztelés<br>CD: deploy Docker Hub-ra |
| **Push to `develop`** | `develop` | ✅ **IGEN** | ❌ NEM | CI: tesztelés<br>CD: csak main-en deploy-ol |
| **Pull Request → `main`** | bármely | ✅ **IGEN** | ❌ NEM | CI: PR tesztelése<br>CD: nincs merge még |
| **Pull Request → `develop`** | bármely | ✅ **IGEN** | ❌ NEM | CI: PR tesztelése<br>CD: nincs merge még |
| **Push to `feature/*`** | feature | ❌ NEM | ❌ NEM | Egyik sem van beállítva |

---

## 📊 Részletes Forgatókönyvek

### 1️⃣ MAIN BRANCH PUSH - MINDKETTŐ FUT

**Példa:**
```bash
git checkout main
git add .
git commit -m "Fix bug"
git push origin main
```

**Mi történik:**
```
Push to main
    ↓
┌───────────────────┐
│   CI Pipeline     │ ✅ FUT
│   - PHP 8.2 test  │
│   - PHP 8.3 test  │
│   - Docker build  │
└───────────────────┘
    ↓
┌───────────────────┐
│   CD Pipeline     │ ✅ FUT
│   - Build image   │
│   - Push to Hub   │
│   - Tag: latest   │
└───────────────────┘
```

**GitHub Actions:**
- ✅ CI - Build and Test (futó)
- ✅ CD - Deploy (futó)

**Időrend:**
1. CI indul (tesztek)
2. CD indul (deploy)
3. Párhuzamosan futnak (nem várják meg egymást)

---

### 2️⃣ DEVELOP BRANCH PUSH - CSAK CI FUT

**Példa:**
```bash
git checkout develop
git add .
git commit -m "Add feature"
git push origin develop
```

**Mi történik:**
```
Push to develop
    ↓
┌───────────────────┐
│   CI Pipeline     │ ✅ FUT
│   - PHP 8.2 test  │
│   - PHP 8.3 test  │
│   - Docker build  │
└───────────────────┘
    ↓
    CD Pipeline      ❌ NEM FUT
    (csak main-en)
```

**GitHub Actions:**
- ✅ CI - Build and Test (futó)
- ❌ CD - Deploy (nem fut)

**Miért jó ez?**
- Develop: Fejlesztés és tesztelés
- Main: Production-ready kód
- Docker Hub-ra csak production-ready kódot push-olunk

---

### 3️⃣ PULL REQUEST - CSAK CI FUT

**Példa:**
```bash
# Feature branch
git checkout -b feature/new-api
git add .
git commit -m "Add new endpoint"
git push origin feature/new-api

# GitHub-on: Create Pull Request → main
```

**Mi történik:**
```
Pull Request opened/updated
    ↓
┌───────────────────┐
│   CI Pipeline     │ ✅ FUT
│   - PHP 8.2 test  │
│   - PHP 8.3 test  │
│   - Docker build  │
│   ✅ vagy ❌     │
└───────────────────┘
    ↓
    CD Pipeline      ❌ NEM FUT
    (PR nincs merge-elve)
```

**GitHub Actions:**
- ✅ CI - Build and Test (PR check)
- ❌ CD - Deploy (nem fut)

**Miért jó ez?**
- PR: Code review előtt tesztelünk
- Ha CI ❌ FAIL → nem merge-elhető
- Ha CI ✅ PASS → merge-elhető
- Merge után (main-en) fut a CD

---

## 🔄 Teljes Development Workflow

### Eset 1: Feature Development → Production

```
1. Feature branch létrehozása
   git checkout -b feature/auth

   → Nincs pipeline

2. Kód írása és commit
   git commit -m "Add authentication"

   → Nincs pipeline

3. Push feature branch
   git push origin feature/auth

   → Nincs pipeline (nincs beállítva)

4. Pull Request → main
   GitHub: Create PR

   → ✅ CI fut (tesztelés)
   → ❌ CD NEM fut

5. PR Merge (ha CI ✅ pass)
   GitHub: Merge Pull Request

   → ✅ CI fut (main push)
   → ✅ CD fut (deploy)
   → Docker Hub-on új image: latest
```

### Eset 2: Hotfix (gyors javítás)

```
1. Közvetlenül main-en dolgozol
   git checkout main
   git add bug-fix.php
   git commit -m "Fix critical bug"

   → Nincs pipeline még

2. Push to main
   git push origin main

   → ✅ CI fut (tesztelés)
   → ✅ CD fut (deploy)
   → Automatikus production release
```

### Eset 3: Develop branch használata

```
1. Új funkció develop-on
   git checkout develop
   git add new-feature.php
   git commit -m "Add feature"
   git push origin develop

   → ✅ CI fut (tesztelés)
   → ❌ CD NEM fut

2. Testing/staging környezet
   Develop branch: tesztelés alatt álló kód

3. Amikor kész, merge develop → main
   git checkout main
   git merge develop
   git push origin main

   → ✅ CI fut (tesztelés)
   → ✅ CD fut (production deploy)
```

---

## ⚙️ Pipeline Konfigurációk

### CI Pipeline (`.github/workflows/ci.yml`)
```yaml
on:
  pull_request:
    branches: [ main, develop ]   # PR esetén
  push:
    branches: [ main, develop ]    # Push esetén
```

**Trigger események:**
- Push to `main` → ✅ FUT
- Push to `develop` → ✅ FUT
- PR to `main` → ✅ FUT
- PR to `develop` → ✅ FUT

### CD Pipeline (`.github/workflows/cd.yml`)
```yaml
on:
  push:
    branches: [ main ]   # Csak main push
```

**Trigger események:**
- Push to `main` → ✅ FUT
- Push to `develop` → ❌ NEM FUT
- Pull Request → ❌ NEM FUT

---

## 📈 Best Practices

### ✅ Jó Gyakorlatok

1. **Main branch protected**
   - GitHub Settings → Branches → Branch protection rules
   - Require status checks to pass: ✅ CI - Build and Test
   - Így csak sikeres CI után lehet merge-elni

2. **Feature branch workflow**
   ```
   feature/* → PR → CI teszt → Merge → main → CI + CD
   ```

3. **Develop branch staging**
   ```
   develop: tesztelés alatt
   main: production-ready
   ```

### ❌ Kerülendő

1. **Ne push-olj közvetlenül main-re** (ha nem hotfix)
2. **Ne skip-eld a CI-t** (mindig várj a ✅-ra)
3. **Ne merge-elj failed CI-val**

---

## 🎯 Jelenlegi Setup Összefoglalás

### ✅ Aktív Branch-ek:

**`main`** - Production branch
- Push → CI ✅ + CD ✅
- Docker Hub automatic deploy
- Production-ready code only
- **URL:** https://github.com/TatLiliana/devops-todo-project/tree/main

**`develop`** - Staging/Development branch ⭐ **MOST LÉTREHOZVA**
- Push → CI ✅ only (no deploy)
- Test before production
- Development work happens here
- **URL:** https://github.com/TatLiliana/devops-todo-project/tree/develop

### 💡 Feature Branch-ek (létrehozhatók):
- **`feature/*`** - Egyedi funkciók
  - PR → CI ✅ only
  - Code review before merge
  - Example: `feature/add-authentication`

---

## 🔍 Hogyan Ellenőrizd?

### GitHub-on:
1. **Actions tab**: https://github.com/TatLiliana/devops-todo-project/actions
2. Látod az összes futást
3. Szűrés workflow szerint:
   - `CI - Build and Test`
   - `CD - Deploy`

### Commit után:
```bash
git push origin main

# Menj GitHub Actions-re
# Látni fogod:
✅ CI - Build and Test #12 (2 jobs: PHP 8.2, 8.3 + Build)
✅ CD - Deploy #5 (1 job: Deploy to Docker Hub)
```

---

## 💡 Tippek

### Debug CI failure
```bash
# GitHub Actions → Failed workflow → Details
# Nézd meg melyik step fail-t
# Lokálisan futtasd ugyanazt:

# CI teszt lokálisan
php -S localhost:8000 router.php &
curl http://localhost:8000/api/health
```

### Manuális trigger (ha kell)
```yaml
on:
  workflow_dispatch:  # Manual trigger
  push:
    branches: [ main ]
```

GitHub → Actions → CI - Build and Test → Run workflow

---


