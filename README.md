# Blog Application (Symfony + React)

This project implements the blog test assignment using a Symfony 7.4 backend, PostgreSQL, Docker, and a React frontend that interacts with the API via token authentication.

## Requirements

- Docker & Docker Compose
- Node.js 22.x and npm (frontend dev server)

## Backend Setup

1. Copy default envs if needed and start the stack:
   ```bash
docker compose up -d --build
```
2. Run database migrations:
   ```bash
docker compose exec backend-php php bin/console doctrine:migrations:migrate
```
3. Create users via the Symfony form (`http://localhost:8080/user/register`) or the console command:
   ```bash
docker compose exec backend-php php bin/console app:create-admin-user <username> <firstName> <lastName>
```
4. Generate (or retrieve) API tokens when needed. You can query the DB directly, for example:
   ```bash
docker compose exec db psql -U symfony -d symfony_article -c "SELECT id, username, api_token FROM \"user\";"
```
   Copy the token for the user you plan to use with the API.

### Useful Backend URLs

- Symfony form login: `http://localhost:8080/login`
- User registration form: `http://localhost:8080/user/register`
- Article creation form: `http://localhost:8080/article/create`
- API endpoints (`X-AUTH-TOKEN` header required):
  - `GET/POST/PUT/DELETE /api/users`
  - `GET/POST/PUT/DELETE /api/articles`

## Frontend Setup

The React app lives under `frontend/` (Vite + React):

1. Create a `.env` file in `frontend/` (already done in this repo) with:
   ```
VITE_API_BASE_URL=http://localhost:8080
VITE_API_TOKEN=<your_api_token_here>
```
2. Install dependencies and run the dev server:
   ```bash
cd frontend
npm install
npm run dev
```
3. Visit the Vite URL shown in the terminal (usually `http://localhost:5173`). The UI lets you:
   - List/create/delete users and articles via the API.
   - All requests automatically include the `X-AUTH-TOKEN` header using the token from the `.env` file.
