# BookLoop — test script

Work through this list before you present. Tick every line.
Anything that fails is a bug to fix, not a line to skip.

Use **two accounts in two different browsers** (for example Chrome and Firefox).
Several of these tests cannot fail with only one account.

## Accounts
- [ ] Register with an empty form → every error message appears at once
- [ ] Register with a password under 8 characters → refused
- [ ] Register with an email that already exists → refused
- [ ] Register correctly → logged in, name shows in the navbar
- [ ] Log out → the login links come back
- [ ] Log in with the wrong password → one clear message
- [ ] Open `account.php` while logged out → sent to the login page

## Books
- [ ] Add a book with an empty title → refused
- [ ] Add a book with year `99` → refused
- [ ] Add a book with no year → saved, the column is NULL (not 0)
- [ ] Edit your own book → the change appears
- [ ] Open `edit-book.php?id=` for somebody else's book → refused
- [ ] Delete your own book → gone from the catalog
- [ ] The other account sees no Edit or Delete buttons on your books

## Search
- [ ] Search by a word in the title → found
- [ ] Search by a word in the author → found
- [ ] Search for nonsense → the empty message, with a way back
- [ ] Filter by genre, then combine with a search
- [ ] Change the sort order → the order really changes
- [ ] Page 2 of the catalog keeps your filters

## My library
- [ ] Put a book on a shelf → the badge appears on the catalog card
- [ ] Move it to another shelf → still exactly one row in `user_books`
- [ ] Remove it → the badge disappears
- [ ] The other account's library is unaffected

## Loans
- [ ] Reserve your own book → refused
- [ ] Reserve somebody else's book → the badge turns Reserved
- [ ] A third account tries to reserve the same book → refused
- [ ] The borrower tries to hand over → refused
- [ ] The owner hands over → Borrowed, with a due date
- [ ] Mark returned → Available again, and the history row remains

## Exchange
- [ ] Offer one of your books for exchange → it appears in the exchange catalog
- [ ] Propose a swap with a message
- [ ] Two accounts propose for the same book → both are pending
- [ ] Accept one → the other becomes Rejected by itself
- [ ] Complete the exchange → both `owner_id` values have swapped
- [ ] Each book now appears in the other person's My books

## API and AI
- [ ] Search online for a book you own → results appear
- [ ] Add to my books → the form is pre-filled
- [ ] Disconnect from the internet and search again → a friendly warning
- [ ] Generate a description → the textarea fills in
- [ ] Press it with an empty title → a polite refusal

## Look and feel
- [ ] Every page has the correct title in the browser tab
- [ ] The current page is highlighted in the navbar
- [ ] The site works at 375px wide (phone) — DevTools device toolbar
- [ ] A very long book title doesn't break any card
- [ ] An Armenian title displays correctly everywhere
- [ ] No PHP warnings or notices anywhere on any page
