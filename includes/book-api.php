<?php
// BookLoop — searching the Open Library, a free public book database.
// Docs: https://openlibrary.org/dev/docs/api/search
// No key, no registration, no cost.

const BOOK_API_URL = 'https://openlibrary.org/search.json';

/**
 * Search Open Library and return books in OUR shape, not theirs.
 */
function searchBookApi(string $query, int $limit = 12): ?array
{
    if (trim($query) === '') {
        return [];
    }

    // http_build_query escapes spaces and special characters for us.
    $url = BOOK_API_URL . '?' . http_build_query([
        'q'      => $query,
        'limit'  => $limit,
        'fields' => 'key,title,author_name,first_publish_year,cover_i,subject',
    ]);

    $data = httpGetJson($url);

    if ($data === null || !isset($data['docs'])) {
        return null;      // null means "the request failed", not "no results"
    }

    $books = [];

    foreach ($data['docs'] as $doc) {
        $books[] = [
            'title'  => $doc['title'] ?? 'Untitled',
            'author' => $doc['author_name'][0] ?? '',
            'year'   => $doc['first_publish_year'] ?? '',
            'genre'  => isset($doc['subject'][0]) ? ucfirst($doc['subject'][0]) : '',
            'cover'  => isset($doc['cover_i'])
                        ? 'https://covers.openlibrary.org/b/id/' . $doc['cover_i'] . '-M.jpg'
                        : null,
        ];
    }

    return $books;
}
