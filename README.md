# Todo App

Aplikacja TODO zbudowana w stacku Laravel 11 + Inertia.js + React + TypeScript + TailwindCSS + PostgreSQL.

## Wymagania

- PHP 8.3+
- Composer
- Node.js 20+
- npm lub yarn
- PostgreSQL 16+
- Docker i Docker Compose (opcjonalnie, dla lokalnego developmentu)

## Instalacja lokalna (bez Docker)

### 1. Instalacja zależności PHP

```bash
composer install
```

### 2. Konfiguracja środowiska

Skopiuj plik `.env.example` do `.env`:

```bash
cp .env.example .env
```

Wygeneruj klucz aplikacji:

```bash
php artisan key:generate
```

Skonfiguruj połączenie z bazą danych w pliku `.env`:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=todo_app
DB_USERNAME=postgres
DB_PASSWORD=twoje_haslo
```

### 3. Utworzenie bazy danych

Utwórz bazę danych PostgreSQL:

```bash
createdb todo_app
```

Lub przez psql:

```bash
psql -U postgres
CREATE DATABASE todo_app;
```

### 4. Migracje

Uruchom migracje:

```bash
php artisan migrate
```

### 5. Instalacja zależności Node.js

```bash
npm install
```

### 6. Uruchomienie serwera deweloperskiego

W jednym terminalu uruchom Laravel:

```bash
php artisan serve
```

W drugim terminalu uruchom Vite (dla hot-reload frontendu):

```bash
npm run dev
```

Aplikacja będzie dostępna pod adresem: `http://localhost:8000`

## Instalacja przez Docker

### 1. Skonfiguruj `.env`

Skopiuj `.env.example` do `.env` i ustaw:

```env
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=todo_app
DB_USERNAME=postgres
DB_PASSWORD=postgres
```

### 2. Uruchom kontenery

```bash
docker-compose up -d
```

### 3. Zainstaluj zależności PHP

```bash
docker-compose exec app composer install
```

### 4. Wygeneruj klucz aplikacji

```bash
docker-compose exec app php artisan key:generate
```

### 5. Uruchom migracje

```bash
docker-compose exec app php artisan migrate
```

### 6. Zainstaluj zależności Node.js i zbuduj frontend

```bash
docker-compose exec node npm install
docker-compose exec node npm run build
```

Aplikacja będzie dostępna pod adresem: `http://localhost:8080`

## Struktura aplikacji

### Backend (Laravel)

- **Modele**: `app/Models/Task.php`, `app/Models/Project.php`, `app/Models/Label.php`
- **Kontrolery**: `app/Http/Controllers/TasksController.php`, `app/Http/Controllers/WeeksController.php`
- **Serwisy**: `app/Services/WeekService.php`, `app/Services/SortOrderService.php`
- **Migracje**: `database/migrations/`

### Frontend (React + TypeScript)

- **Strony**: `resources/js/Pages/`
- **Komponenty**: `resources/js/Components/`
- **Typy**: `resources/js/Types/index.ts`
- **Layout**: `resources/js/Layouts/AppLayout.tsx`

## Widoki

### 1. Tygodnie (`/weeks`)

Kanban tygodnia z 7 kolumnami (Poniedziałek–Niedziela). Funkcje:
- Drag & drop zadań między kolumnami
- Sortowanie zadań w kolumnie
- Dodawanie zadań inline
- Oznaczanie zadań jako wykonane
- Oznaczanie zadań gwiazdką
- Wyświetlanie terminów, projektów i etykiet

### 2. Inbox (`/inbox`)

Wszystkie zadania bez projektu, gdzie `bucket='inbox'`.

### 3. Zadania Pojedyncze (`/single`)

Zadania bez projektu, gdzie `bucket='single'`.

### 4. Priorytety (`/priorities`)

Agreguje sekcje:
- **Dzisiaj**: zadania z `scheduled_for=today`
- **Gwiazdka**: zadania z `starred=true`
- **Zaległe**: zadania z `due_date < today`

## API Endpoints

### GET `/api/weeks?start=YYYY-MM-DD`

Zwraca dane tygodnia w formacie JSON:

```json
{
  "weekStart": "2024-01-01",
  "weekEnd": "2024-01-07",
  "days": [
    {
      "date": "2024-01-01",
      "dayName": "Mon",
      "dayNumber": 1,
      "isToday": false,
      "tasks": [...]
    }
  ]
}
```

### POST `/api/tasks`

Tworzy nowe zadanie:

```json
{
  "title": "Nazwa zadania",
  "scheduled_for": "2024-01-01",
  "bucket": "inbox",
  "due_date": "2024-01-05"
}
```

### PATCH `/api/tasks/{id}`

Aktualizuje zadanie:

```json
{
  "scheduled_for": "2024-01-02",
  "starred": true,
  "status": "done"
}
```

## Zasady spójności danych

- Jeśli `project_id != null` => `bucket` automatycznie ustawia się na `'project'`
- Jeśli `project_id == null` => `bucket` może być `'inbox'` albo `'single'`

## Deploy na VPS przez Coolify

1. Połącz repozytorium z Coolify
2. Ustaw zmienne środowiskowe w Coolify:
   - `DB_CONNECTION=pgsql`
   - `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
   - `APP_KEY` (wygeneruj przez `php artisan key:generate`)
3. Coolify automatycznie zbuduje aplikację używając `Dockerfile`
4. Uruchom migracje po pierwszym deploy:
   ```bash
   php artisan migrate
   ```

## ASSUMPTIONS / TODO

- **ASSUMPTION**: Tydzień zaczyna się od poniedziałku
- **ASSUMPTION**: Domyślny bucket dla nowych zadań bez projektu to `'inbox'`
- **ASSUMPTION**: Sortowanie zadań używa wartości float dla `sort_order`
- **TODO**: Rozważyć bardziej zaawansowane sortowanie z przerwami dla przyszłych wstawień
- **TODO**: Dodać obsługę timezone w WeekService dla produkcji
- **TODO**: Dodać autentykację użytkowników (obecnie brak)
- **TODO**: Dodać walidację CSRF token w API requests
- **TODO**: Dodać testy jednostkowe i integracyjne

## Licencja

MIT

