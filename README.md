# BookLoop — Session 7 reference code

Finished state of the project at the end of Session 7 (API & AI).

## Run it

1. Copy the `bookloop` folder into your MAMP `htdocs` folder.
2. In phpMyAdmin run the SQL files in order:
   `database.sql` → `database-session-02.sql` → … → `database-session-06.sql`.
   **Session 7 adds no new tables.**
3. Open http://localhost:8888/bookloop/

### Requirements

- **cURL** must be enabled in PHP. MAMP and most Linux builds have it on by
  default; on XAMPP uncomment `extension=curl` in `php.ini` and restart Apache.
- The **Open Library** search needs an internet connection but no key.
- The **AI features** need an API key (see below). Without one the site works
  normally and the two AI buttons simply don't appear.

### Adding an AI key

```
cp config.local.example.php config.local.php
# then edit config.local.php and paste your key
```

`config.local.php` is listed in `.gitignore`. Never commit, email or upload it.
If a key leaks, revoke it in the provider's dashboard and generate a new one.

The provider is set in `includes/ai.php` (`AI_URL`, `AI_MODEL`). It is written
for the Anthropic Messages API; adapting it to another provider means changing
those two constants, the auth header, and the line that reads the response text.

## New in this session

| File | Purpose |
|------|---------|
| `includes/http.php` | `httpGetJson()`, `httpPostJson()` — cURL wrapped once, with timeouts |
| `includes/book-api.php` | `searchBookApi()` — Open Library, converted into our own shape |
| `includes/ai.php` | `aiEnabled()`, `askAi()`, `aiDescribeBook()`, `aiRecommend()` |
| `api-search.php` | Search the Open Library and pre-fill the add form |
| `ai-description.php` | JSON endpoint called by `fetch()` from the book form |
| `recommend.php` | Describe your taste, get books from the catalog |
| `config.local.example.php` | Template for the key file |

Changed: `add-book.php` accepts pre-filled fields from `$_GET`;
`includes/book-form.php` has the ✨ button and the `fetch()` call;
`includes/header.php` has the "✨ For you" link; `config.php` loads the new
helpers and the optional key file.

## Notes for teaching

- Everything AI-related is wrapped in `aiEnabled()`, so a student without a key
  still has a fully working project.
- `searchBookApi()` returns `null` when the request fails and `[]` when it
  succeeds with no matches — the page shows a different message for each.
- Ids returned by the AI in `recommend.php` are looked up in the database one by
  one. An id the model invented is silently dropped.
