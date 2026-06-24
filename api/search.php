<?php
// =====================================================
// Smart Salon Search – Keyword Matching (No API)
// =====================================================

require_once '../config.php';
header('Content-Type: application/json');

$query = isset($_GET['query']) ? trim($_GET['query']) : '';
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 6;

if (strlen($query) < 2) {
    echo json_encode([
        'error' => 'Please enter at least 2 characters',
        'results' => []
    ]);
    exit;
}

// =============================================
// FETCH ALL SALONS FROM DATABASE
// =============================================
$conn = getDB();
$salons = [];
$result = $conn->query("SELECT * FROM salons WHERE is_admin = 0 ORDER BY rating DESC");
while ($row = $result->fetch_assoc()) {
    // Get image URL (now works from API)
    $row['image'] = getSalonImage($row['name']);
    $salons[] = $row;
}

if (empty($salons)) {
    echo json_encode([
        'results' => [],
        'message' => 'No salons found',
        'query' => $query
    ]);
    exit;
}

// =============================================
// SMART KEYWORD SEARCH
// =============================================
$scoredSalons = [];
$queryLower = strtolower($query);
$keywords = array_filter(array_map('trim', explode(' ', $queryLower)));

foreach ($salons as $salon) {
    $score = 0;
    $matchedKeywords = [];
    
    // Build searchable text
    $searchableText = strtolower(
        ($salon['name'] ?? '') . ' ' .
        ($salon['locality'] ?? '') . ' ' .
        ($salon['tagline'] ?? '') . ' ' .
        ($salon['description'] ?? '') . ' ' .
        ($salon['services'] ?? '')
    );
    
    foreach ($keywords as $keyword) {
        if (strlen($keyword) < 2) continue;
        
        // --- EXACT MATCH ---
        if (strpos($searchableText, $keyword) !== false) {
            $score += 5;
            $matchedKeywords[] = $keyword;
        }
        // --- PARTIAL MATCH ---
        elseif (preg_match('/\b' . preg_quote($keyword, '/') . '\w*/i', $searchableText)) {
            $score += 3;
            $matchedKeywords[] = $keyword;
        }
        // --- WILDCARD MATCH ---
        elseif (strpos($searchableText, substr($keyword, 0, 3)) !== false) {
            $score += 1;
            $matchedKeywords[] = $keyword;
        }
    }
    
    // --- LOCALITY MATCH (HIGH PRIORITY) ---
    foreach ($keywords as $kw) {
        if (strlen($kw) > 2 && stripos($salon['locality'], $kw) !== false) {
            $score += 10;
            $matchedKeywords[] = $kw . ' (locality)';
        }
    }
    
    // --- SERVICE MATCH ---
    if ($salon['services']) {
        foreach ($keywords as $keyword) {
            if (strlen($keyword) > 2 && stripos($salon['services'], $keyword) !== false) {
                $score += 4;
                $matchedKeywords[] = $keyword . ' (service)';
            }
        }
    }
    
    // --- BOOST SCORES ---
    if ($salon['rating'] >= 4.5) $score += 3;
    elseif ($salon['rating'] >= 4.0) $score += 2;
    elseif ($salon['rating'] >= 3.5) $score += 1;
    
    if ($salon['verified']) $score += 2;
    if ($salon['rain_safe']) $score += 1;
    
    // --- PRICE MATCH ---
    if (preg_match('/\b(\d+)\s*[–\-]\s*(\d+)\b/', $query, $priceMatch)) {
        $min = (int)$priceMatch[1];
        $max = (int)$priceMatch[2];
        if ($salon['price_min'] >= $min && $salon['price_max'] <= $max) {
            $score += 3;
        }
    }
    if (preg_match('/\b(\d+)\b/', $query, $singlePrice)) {
        $price = (int)$singlePrice[1];
        if ($price >= 100 && $price <= 50000) {
            if ($salon['price_min'] <= $price && $salon['price_max'] >= $price) {
                $score += 2;
            }
        }
    }
    
    // Only include if score > 0
    if ($score > 0) {
        $scoredSalons[] = [
            'salon' => $salon,
            'score' => $score,
            'matched' => array_unique($matchedKeywords)
        ];
    }
}

// Sort by score (highest first), then by rating
usort($scoredSalons, function($a, $b) {
    if ($b['score'] == $a['score']) {
        return $b['salon']['rating'] - $a['salon']['rating'];
    }
    return $b['score'] - $a['score'];
});

// Minimum score threshold – only return relevant results
$maxScore = !empty($scoredSalons) ? $scoredSalons[0]['score'] : 0;
$threshold = $maxScore * 0.3;

$filteredSalons = array_filter($scoredSalons, function($item) use ($threshold) {
    return $item['score'] >= $threshold;
});

$filteredSalons = array_slice($filteredSalons, 0, $limit);

$results = array_map(function($item) {
    return $item['salon'];
}, $filteredSalons);

// =============================================
// RESPONSE
// =============================================
echo json_encode([
    'results' => $results,
    'count' => count($results),
    'total_scored' => count($scoredSalons),
    'filtered_count' => count($filteredSalons),
    'query' => $query,
    'message' => count($results) > 0 ? 'Found ' . count($results) . ' salon(s)' : 'No matching salons found'
]);