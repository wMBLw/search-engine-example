#Search Engine Example



[![Tests](https://img.shields.io/badge/tests-Pest-passing?style=flat-square&logo=php)](https://pestphp.com/)
[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat-square&logo=php)](https://www.php.net/)
[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=flat-square&logo=laravel)](https://laravel.com/)
[![Docker](https://img.shields.io/badge/Docker-Ready-2496ED?style=flat-square&logo=docker)](https://www.docker.com/)
[![License](https://img.shields.io/badge/License-MIT-green.svg?style=flat-square)](LICENSE)

---

## İçindekiler

- [Teknoloji Yığını](#-teknoloji-yığını)
- [Kurulum](#-kurulum)
- [API Dokümantasyonu](#-api-dokümantasyonu)
- [Kullanılan Tasarım Desenleri](#-kullanılan-tasarım-desenleri)
- [Proje Yapısı](#-proje-yapisi)
- [Konseptler](#-konseptler)

---

## Teknoloji Yığını

### Backend
- **PHP 8.2+**
- **Laravel 12.x**
- **MySQL 8.0** - Veritabanı
- **Redis 7.2** - Cache ve Queue
- **Guzzle HTTP** - API istekleri

### DevOps
- **Docker & Docker Compose** - Containerization
- **Nginx** - Web server
- **Supervisor** - Process management

### Testing & Quality
- **Pest PHP** - Testing framework
- **GitHub Actions** - CI/CD

### Monitoring & Debugging
- **Laravel Telescope** - Application debugging
- **Laravel Horizon** - Queue monitoring

---

## Kurulum

### Gereksinimler

- Docker ve Docker Compose

### Kurulum Adımları

**1. Projeyi klonlayın:**
```bash
git clone https://github.com/wMBLw/search-engine-example.git
cd search-engine-example/docker
```

**2. Environment dosyasını oluşturun:**
```bash
cp ../.env.example ../.env
```

> **Not:** .env.example dosyası hazır ayarlarla gelir, değişiklik yapmanıza gerek yoktur.

**3. Docker container'ları başlatın:**
```bash
docker compose --env-file ../.env up -d --build
```

**4. Tamamlandı**

Uygulama otomatik olarak:
- Bağımlılıkları yükler (composer, npm)
- Veritabanını oluşturur ve migrate eder
- Seed verilerini yükler

### Erişim Bilgileri

**API Base URL:** http://localhost/api

**Test Kullanıcısı:**
- Email: test@example.com
- Şifre: password

**Servisler:**
- **Web Application:** http://localhost
- **Telescope (Debug):** http://localhost/telescope
- **Horizon (Queue):** http://localhost/horizon
- **MySQL:** localhost:3306
- **Redis:** localhost:6379

---

## API Dokümantasyonu

### Postman Collection

Proje içinde hazır Postman collection ve environment dosyaları bulunmaktadır:
- postman/SearchEngineExample.postman_collection.json
- postman/SearchEngineExample.postman_environment.json

### Endpoints

#### Authentication

**1. Login**
```http
POST /api/login
Content-Type: application/json

{
  "email": "test@example.com",
  "password": "password"
}
```

**Response (200):**
```json
{
  "data": {
    "user": {
      "id": 1,
      "name": "Test User",
      "email": "test@example.com"
    },
    "access_token": "1|xxxxxxxxxxxxx",
    "refresh_token": "2|xxxxxxxxxxxxx",
    "access_token_expires_at": "2025-10-03T13:00:00.000000Z",
    "refresh_token_expires_at": "2025-11-02T12:00:00.000000Z"
  }
}
```

**2. Refresh Token**
```http
POST /api/refresh-token
Authorization: Bearer {refresh_token}
```

**3. Get Logged In User**
```http
GET /api/user
Authorization: Bearer {access_token}
```

**4. Logout**
```http
GET /api/user/logout
Authorization: Bearer {access_token}
```

#### Search

**5. Search Contents**
```http
GET /api/search?keyword=laravel&type=article&sort_by=score&per_page=10&page=1
Authorization: Bearer {access_token}
```

**Query Parameters:**

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| keyword | string | No | - | Aranacak kelime (min: 2, max: 255) |
| type | string | No | - | İçerik tipi: video, article |
| sort_by | string | No | score | Sıralama: score, title, views, likes, published_at |
| sort_direction | string | No | desc | Sıralama yönü: asc, desc |
| per_page | integer | No | 20 | Sayfa başına kayıt (min: 1, max: 100) |
| page | integer | No | 1 | Sayfa numarası |

**Response (200):**
```json
{
  "data": [
    {
      "id": 1,
      "external_id": "ext-123",
      "title": "Laravel 12 Yenilikleri",
      "type": "article",
      "views": 15000,
      "likes": 1200,
      "tags": ["laravel", "php", "framework"],
      "published_at": "2025-10-01T10:00:00.000000Z",
      "score": 125.75,
      "provider": {
        "id": 1,
        "name": "Provider 1"
      }
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 10,
    "total": 100
  }
}
```

**6. Get Statistics**
```http
GET /api/search/statistics
Authorization: Bearer {access_token}
```

**Response (200):**
```json
{
  "data": {
    "total_contents": 1250,
    "total_videos": 750,
    "total_articles": 500,
    "total_providers": 2,
    "active_providers": 2
  }
}
```


---

## Kullanılan Tasarım Desenleri

### 1.Abstract Factory Pattern

**Kullanım Alanı:** Provider Adapter'ları oluşturmak için

**Amaç:** Farklı veri formatlarını (JSON, XML) destekleyen provider adapter'larını dinamik olarak oluşturmak.

```
┌────────────────────────────────────────┐
│    ProviderAdapterFactory              │
│    (Abstract Factory)                  │
└────────────────────────────────────────┘
                 │
                 │ creates
                 ▼
┌────────────────────────────────────────┐
│    AbstractProviderAdapter             │
│    (Abstract Product)                  │
└────────────────────────────────────────┘
                 △
                 │ implements
        ┌────────┴────────┐
        │                 │
┌───────────────┐  ┌──────────────┐
│ JsonProvider  │  │ XmlProvider  │
│   Adapter     │  │   Adapter    │
└───────────────┘  └──────────────┘
```

---

### 2.Adapter Pattern

**Kullanım Alanı:** Dış API'lerden gelen farklı formatları normalize etmek

**Amaç:** JSON ve XML formatındaki farklı veri yapılarını ortak bir formata dönüştürmek.

```
External APIs            Adapters              Application
┌──────────┐         ┌──────────────┐      ┌──────────────┐
│ JSON API │────────>│ JsonAdapter  │─────>│              │
└──────────┘         └──────────────┘      │  Normalized  │
┌──────────┐         ┌──────────────┐      │   Content    │
│ XML API  │────────>│ XmlAdapter   │─────>│     DTO      │
└──────────┘         └──────────────┘      └──────────────┘
```

---

### 3.Strategy Pattern

**Kullanım Alanı:** İçerik puanlama (scoring) algoritmaları

**Amaç:** Video ve Article içerikleri için farklı puanlama algoritmaları uygulamak.

```
┌────────────────────────────────┐
│   ScoringStrategyFactory       │
└────────────────────────────────┘
                │
                │ creates
                ▼
┌────────────────────────────────┐
│   ScoringStrategyInterface     │
└────────────────────────────────┘
                △
                │ implements
        ┌───────┴────────┐
        │                │
┌───────────────┐  ┌────────────────┐
│ VideoScoring  │  │ ArticleScoring │
│   Strategy    │  │    Strategy    │
└───────────────┘  └────────────────┘
```


---

### 4.Chain of Responsibility Pattern

**Kullanım Alanı:** Arama filtreleri

**Amaç:** Arama sorgusuna birden fazla filtreyi zincirleme şekilde uygulamak.

```
Search Query
      │
      ▼
┌──────────────────┐
│  KeywordFilter   │ → keyword varsa filtrele
└──────────────────┘
      │
      ▼
┌──────────────────┐
│ ContentTypeFilter│ → type varsa filtrele
└──────────────────┘
      │
      ▼
┌──────────────────┐
│  SortingFilter   │ → sıralama uygula
└──────────────────┘
      │
      ▼
   Final Result
```
---

### 5.Circuit Breaker Pattern

**Kullanım Alanı:** Provider senkronizasyonu hata yönetimi

**Amaç:** Sürekli başarısız olan provider'ları geçici olarak devre dışı bırakmak.

```
┌─────────────────────────────────────────────────┐
│              Circuit Breaker States              │
└─────────────────────────────────────────────────┘

     ┌──────────┐
     │  CLOSED  │  (Normal çalışma)
     └──────────┘
          │
          │ Başarısız istekler artıyor
          │ (consecutive_failures >= 3)
          ▼
     ┌──────────┐
     │   OPEN   │  (Devre dışı - 30 dakika)
     └──────────┘
          │
          │ Timeout süresi doldu
          ▼
     ┌──────────┐
     │  CLOSED  │  (Tekrar aktif)
     └──────────┘
```

**Veritabanı Yapısı:**
```sql
providers
├── consecutive_failures (int)    -- Ardışık hata sayısı
└── disabled_until (timestamp)    -- Devre dışı kalma süresi
```
---

### 6.Distributed Lock Pattern

**Kullanım Alanı:** Provider senkronizasyonu

**Amaç:** Aynı provider'ın aynı anda birden fazla process tarafından senkronize edilmesini önlemek.

```
┌──────────────┐         ┌──────────────┐         ┌──────────────┐
│  Process 1   │         │  Process 2   │         │  Process 3   │
└──────────────┘         └──────────────┘         └──────────────┘
        │                        │                        │
        │ Lock Al                │ Lock Almaya Çalış     │ Lock Almaya Çalış
        ▼                        ▼                        ▼
┌────────────────────────────────────────────────────────────────┐
│                         Redis Lock                              │
│                 provider_sync_1: uuid-xxx-xxx                   │
└────────────────────────────────────────────────────────────────┘
        │                        │                        │
        │ ✅ Lock Alındı         │ ❌ Bekle              │ ❌ Bekle
        ▼                        │                        │
    İşlem Yap                    │                        │
        │                        │                        │
        │ Lock Serbest Bırak     │                        │
        ▼                        ▼                        ▼
    ✅ Tamamlandı           ✅ Şimdi Alabilir        ✅ Şimdi Alabilir
```

---

### 7.Observer Pattern

**Kullanım Alanı:** Content modeli değişikliklerinde cache yönetimi

---

### 8.Repository Pattern

**Kullanım Alanı:** Veri erişim katmanı

**Amaç:** Business logic ile veri erişim mantığını ayırmak.

---

### 9.Event-Driven Architecture

**Kullanım Alanı:** Kullanıcı login loglama

**Akış:**
```
Login → Event Dispatch → Listener → Queue Job → Database
```

---

### CI/CD Pipeline

GitHub Actions master branch'e PR açıldığında otomatik test çalıştırır:

```yaml
# .github/workflows/run-tests.yml
name: Run Pest Tests

on:
  pull_request:
    branches: [ master ]

jobs:
  laravel-tests:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - run: composer install
      - run: ./vendor/bin/pest
```

---

## Proje Yapısı

```
search-engine-example/
│
├── app/
│   ├── Console/Commands/      # Artisan komutları
│   ├── Enums/                 # Enum'lar
│   ├── Events/                # Event'ler
│   ├── Exceptions/            # Custom exception'lar
│   ├── Filters/               # Chain of Responsibility
│   ├── Http/
│   │   ├── Controllers/
│   │   ├── Requests/
│   │   └── Resources/
│   ├── Jobs/                  # Queue jobs
│   ├── Listeners/             # Event listeners
│   ├── Models/
│   ├── Observers/             # Observer pattern
│   ├── Repositories/          # Repository pattern
│   └── Services/
│       ├── Auth/
│       ├── Content/           # Sync, Lock
│       ├── Providers/         # Adapter, Factory
│       └── Search/            # Strategy pattern
│
├── config/
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
│
├── docker/
│   ├── docker-compose.yml
│   ├── Dockerfile
│   ├── entrypoint.sh
│   └── supervisord.conf
│
├── routes/
│   ├── api.php
│   └── web.php
│
├── tests/
│   ├── Feature/
│   └── Unit/
│
└── .github/workflows/         # CI/CD
    └── run-tests.yml
```

---

## Mimari Diyagramlar

### Provider Sync Akışı

```
Artisan Command
      │
      ▼
┌──────────────────────┐
│ContentSyncService    │
└──────────────────────┘
      │
      ▼
┌──────────────────────┐
│ Distributed Lock     │
│   Acquire Lock       │──── ❌ Skip if locked
└──────────────────────┘
      │
      │ ✅ Lock Alındı
      ▼
┌──────────────────────┐
│ Provider Adapter     │
│  Factory.make()      │
└──────────────────────┘
      │
      ├──→ JSON Adapter
      └──→ XML Adapter
           │
           ▼
┌──────────────────────┐
│   External API       │
│  (JSON/XML Data)     │
└──────────────────────┘
           │
           ▼
┌──────────────────────┐
│ NormalizedContentDTO │
└──────────────────────┘
           │
           ▼
┌──────────────────────┐
│   Content Model      │
│    (Database)        │
└──────────────────────┘
           │
           ▼
┌──────────────────────┐
│  ContentObserver     │
│ clearStatisticsCache │
└──────────────────────┘
```

### Arama Akışı

```
User Request
      │
      ▼
┌──────────────────────┐
│  SearchController    │
└──────────────────────┘
      │
      ▼
┌──────────────────────┐
│   SearchService      │
└──────────────────────┘
      │
      ▼
┌──────────────────────┐
│  SearchRepository    │
└──────────────────────┘
      │
      ▼
┌──────────────────────────────────────┐
│   Pipeline (Chain of Responsibility) │
│                                      │
│  Query → KeywordFilter               │
│       → ContentTypeFilter            │
│       → SortingFilter                │
└──────────────────────────────────────┘
      │
      ▼
┌──────────────────────┐
│      Database        │
└──────────────────────┘
      │
      ▼
┌──────────────────────┐
│  ScoreCalculator     │
│  (Strategy Pattern)  │
└──────────────────────┘
      │
      ▼
┌──────────────────────┐
│  SearchResultDTO     │
└──────────────────────┘
```

---

## Uygulanan Konseptler

### Tasarım Desenleri
- Abstract Factory Pattern
- Adapter Pattern
- Strategy Pattern
- Chain of Responsibility Pattern
- Circuit Breaker Pattern
- Observer Pattern
- Repository Pattern
- Factory Pattern

### Yazılım Prensipleri
- SOLID Prensipleri
- Clean Code
- KISS
- DRY
- Dependency Injection

### Mimari Konseptler
- Layered Architecture
- Event-Driven Architecture
- DTO
- Service Layer Pattern
- Distributed Lock
- Race Condition Prevention
- Circuit Breaker

### Laravel Özellikleri
- Sanctum Authentication (JWT)
- Eloquent ORM & Relationships
- Query Scopes (Global & Local)
- Model Observers
- Events & Listeners
- Queue Jobs (Redis)
- Laravel Horizon
- Laravel Telescope
- Service Providers
- Middleware
- Custom Artisan Commands


### Testing
- Pest PHP Testing Framework
- Unit Tests
- Feature Tests

---

## Önemli Komutlar

### Artisan Commands

```bash
# Seed data
php artisan db:seed

# Provider senkronizasyonu
php artisan providers:sync

```

### Docker Commands

```bash
# Container'ları başlat
docker compose --env-file ../.env up -d --build

# Container'ları durdur
docker compose down
```

---
