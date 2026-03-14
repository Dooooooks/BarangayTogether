# BarangayTogether (PHP + Tailwind + Supabase)

## Requirements
- PHP 8+ (for the built-in dev server)
- Node.js 18+ (for Tailwind CLI)
- A Supabase project (Auth + Postgres)

## Setup
```bash
npm install
```

Copy env and set your Supabase values:
```bash
cp .env.example .env
```

Run the SQL schema in Supabase:
- `supabase/schema.sql`

## Build CSS
```bash
npm run build
```

## Watch CSS (during development)
```bash
npm run dev
```

## Run PHP server
```bash
php -S localhost:8000 -t public
```

Open http://localhost:8000

## Admin
- Create a normal account first.
- In Supabase SQL editor, promote it:
  `update public.profiles set role = 'admin' where email = 'your@email.com';`
