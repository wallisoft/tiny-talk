// ============================================
// TINY-TALK - Universal Emoji Language System
// ============================================
$db_path = 'tinytalk.db';

// Initialize or connect to SQLite database
function initDatabase($db) {
    // Symbols table - core emoji vocabulary
    $db->exec("CREATE TABLE IF NOT EXISTS symbols (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        emoji TEXT UNIQUE,
        meaning TEXT,
        category TEXT,
        keywords TEXT,
        complexity INTEGER DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Check if symbols exist
    $count = $db->querySingle("SELECT COUNT(*) FROM symbols");
    
    if ($count == 0) {
        // Insert core symbols (optimized for small screens)
        $core_symbols = [
            // People & Actions
            ['👤', 'person/agent', 'person', 'person, human, they', 1],
            ['👁️', 'I/self/see', 'person', 'I, me, self, see, look', 1],
            ['👥', 'people/group', 'person', 'group, team, they, them', 1],
            ['🏃', 'move/go', 'action', 'go, run, move, travel', 1],
            ['👄', 'speak/eat', 'action', 'say, eat, speak, mouth', 1],
            ['✋', 'do/make', 'action', 'do, make, hand, action', 1],
            ['🧠', 'think/know', 'action', 'think, know, brain, learn', 2],
            ['👂', 'hear/listen', 'action', 'hear, listen, ear', 1],
            
            // Objects & Things
            ['🏠', 'place/home', 'object', 'home, house, place, building', 1],
            ['🍎', 'thing/food', 'object', 'food, apple, thing, object', 1],
            ['💧', 'water/drink', 'object', 'water, drink, liquid', 1],
            ['📖', 'book/knowledge', 'object', 'book, read, knowledge, learn', 1],
            ['💰', 'value/money', 'object', 'money, value, coin, price', 1],
            ['🛏️', 'sleep/rest', 'object', 'bed, sleep, rest, night', 1],
            ['🚪', 'door/enter', 'object', 'door, enter, exit, open', 1],
            ['🌳', 'tree/nature', 'object', 'tree, plant, nature, grow', 1],
            
            // Modifiers & Properties
            ['🔺', 'big/more', 'modifier', 'big, large, more, increase', 1],
            ['🔻', 'small/less', 'modifier', 'small, little, less, decrease', 1],
            ['🔴', 'red/color', 'modifier', 'red, color, stop, hot', 1],
            ['🟢', 'green/color', 'modifier', 'green, color, go, ok', 1],
            ['🔵', 'blue/color', 'modifier', 'blue, color, cool, water', 1],
            ['❤️', 'love/want/feel', 'modifier', 'love, want, like, feel, heart', 1],
            ['⭐', 'important/good', 'modifier', 'star, good, important, best', 1],
            ['⚡', 'fast/energy', 'modifier', 'fast, quick, energy, power', 1],
            
            // Position & Grammar Markers
            ['➡️', 'does action/agent', 'grammar', 'to, does, agent, forward', 1],
            ['⬅️', 'receives action/object', 'grammar', 'object, receives, backward', 1],
            ['⬆️', 'above/on/more', 'grammar', 'above, on, over, up, more', 1],
            ['⬇️', 'below/under/action-on', 'grammar', 'below, under, down, action-on', 1],
            ['🔲', 'subject start', 'grammar', 'subject start, begin, wrapper', 1],
            ['🔳', 'subject end', 'grammar', 'subject end, finish, wrapper', 2],
            ['🔄', 'change/time', 'grammar', 'change, time, again, repeat', 2],
            ['📍', 'location/here', 'grammar', 'here, location, place, spot', 1],
            
            // Abstract Concepts
            ['❓', 'question/ask', 'abstract', 'question, ask, what, why', 1],
            ['❗', 'emphasis/important', 'abstract', 'important, emphasis, wow', 1],
            ['➕', 'add/and', 'abstract', 'add, and, plus, also', 1],
            ['➖', 'remove/not', 'abstract', 'remove, not, minus, without', 2],
            ['💡', 'idea/light', 'abstract', 'idea, light, think, smart', 2],
            ['🛡️', 'protect/safe', 'abstract', 'protect, safe, shield, defend', 2],
        ];
        
        $stmt = $db->prepare("INSERT INTO symbols (emoji, meaning, category, keywords, complexity) VALUES (?, ?, ?, ?, ?)");
        
        foreach ($core_symbols as $symbol) {
            $stmt->bindValue(1, $symbol[0], SQLITE3_TEXT);
            $stmt->bindValue(2, $symbol[1], SQLITE3_TEXT);
            $stmt->bindValue(3, $symbol[2], SQLITE3_TEXT);
            $stmt->bindValue(4, $symbol[3], SQLITE3_TEXT);
            $stmt->bindValue(5, $symbol[4], SQLITE3_INTEGER);
            $stmt->execute();
        }
    }
    
    // Phrases table - common combinations
    $db->exec("CREATE TABLE IF NOT EXISTS phrases (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        emoji_sequence TEXT,
        english TEXT,
        complexity INTEGER DEFAULT 1,
        usage_count INTEGER DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    $phrase_count = $db->querySingle("SELECT COUNT(*) FROM phrases");
    
    if ($phrase_count == 0) {
        $common_phrases = [
            // Level 1 - Basic
            ['👁️ ❤️ 🍎', 'I want food', 1],
            ['👤 🏃', 'Person goes', 1],
            ['👁️ 👄 🍎', 'I eat food', 1],
            ['🏠 🔺', 'Big house', 1],
            
            // Level 2 - With grammar markers
            ['👁️ ❤️ ⬇️ 🍎', 'I want food (emphasized)', 2],
            ['👤 ➡️ 👄 ⬇️ 🍎', 'Person eats food', 2],
            ['👁️ 🏃 ➡️ 🏠', 'I go to home', 2],
            ['🍎 🔴', 'Red apple', 2],
            
            // Level 3 - Complex
            ['👁️ ❤️ 🏃 ➡️ 🏠 📍', 'I want to go home now', 3],
            ['🔲👤🔳 ➡️ 👁️ ⬇️ 📖', 'Person reads book', 3],
            ['👁️ 🧠 ➡️ 💡', 'I think of idea', 3],
            ['👁️ 👂 ⬇️ 👤', 'I listen to person', 3],
        ];
        
        $stmt = $db->prepare("INSERT INTO phrases (emoji_sequence, english, complexity) VALUES (?, ?, ?)");
        
        foreach ($common_phrases as $phrase) {
            $stmt->bindValue(1, $phrase[0], SQLITE3_TEXT);
            $stmt->bindValue(2, $phrase[1], SQLITE3_TEXT);
            $stmt->bindValue(3, $phrase[2], SQLITE3_INTEGER);
            $stmt->execute();
        }
    }
    
    // User submissions table
    $db->exec("CREATE TABLE IF NOT EXISTS submissions (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        emoji_sequence TEXT,
        english TEXT,
        ip_hash TEXT,
        votes INTEGER DEFAULT 0,
        status TEXT DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    return true;
}

// Connect to database
try {
    $db = new SQLite3($db_path);
    initDatabase($db);
} catch (Exception $e) {
    die("Database error: " . $e->getMessage());
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'translate_text') {
        $text = strtolower(trim($_POST['text']));
        
        // Simple translation rules
        $translation_map = [
            // Pronouns & people
            'i' => '👁️',
            'me' => '👁️',
            'myself' => '👁️',
            'person' => '👤',
            'people' => '👥',
            'he' => '👤',
            'she' => '👤',
            'they' => '👥',
            
            // Actions
            'want' => '❤️',
            'love' => '❤️',
            'like' => '❤️',
            'go' => '🏃',
            'run' => '🏃',
            'move' => '🏃',
            'eat' => '👄',
            'drink' => '💧',
            'speak' => '👄',
            'say' => '👄',
            'talk' => '👄',
            'do' => '✋',
            'make' => '✋',
            'think' => '🧠',
            'know' => '🧠',
            'hear' => '👂',
            'listen' => '👂',
            'read' => '👁️⬇️',
            
            // Objects
            'home' => '🏠',
            'house' => '🏠',
            'food' => '🍎',
            'apple' => '🍎',
            'water' => '💧',
            'drink' => '💧',
            'book' => '📖',
            'money' => '💰',
            'bed' => '🛏️',
            'sleep' => '🛏️',
            'door' => '🚪',
            'tree' => '🌳',
            
            // Modifiers
            'big' => '🔺',
            'large' => '🔺',
            'small' => '🔻',
            'little' => '🔻',
            'red' => '🔴',
            'green' => '🟢',
            'blue' => '🔵',
            'good' => '⭐',
            'great' => '⭐',
            'best' => '⭐',
            'fast' => '⚡',
            'quick' => '⚡',
            
            // Grammar words
            'to' => '➡️',
            'on' => '⬆️',
            'over' => '⬆️',
            'under' => '⬇️',
            'below' => '⬇️',
            'now' => '📍',
            'here' => '📍',
            'change' => '🔄',
            'again' => '🔄',
            'question' => '❓',
            'what' => '❓',
            'why' => '❓',
            'important' => '❗',
            'and' => '➕',
            'also' => '➕',
            'not' => '➖',
            'without' => '➖',
            'idea' => '💡',
            'protect' => '🛡️',
            'safe' => '🛡️',
        ];
        
        // Add grammar markers for certain patterns
        $words = explode(' ', $text);
        $result = [];
        
        foreach ($words as $word) {
            if (isset($translation_map[$word])) {
                $result[] = $translation_map[$word];
            }
        }
        
        // Add position markers for clarity (simple heuristic)
        if (count($result) >= 2) {
            // If we have action + object, add position marker
            $emoji_string = implode(' ', $result);
            if (strpos($emoji_string, '👄') !== false && strpos($emoji_string, '🍎') !== false) {
                // Eat food pattern - add action marker
                $result = ['👁️', '👄', '⬇️', '🍎'];
            }
            if (strpos($emoji_string, '🏃') !== false && strpos($emoji_string, '🏠') !== false) {
                // Go to home pattern
                $result = ['👁️', '🏃', '➡️', '🏠'];
            }
        }
        
        echo json_encode(['emoji' => implode(' ', $result)]);
        exit;
    }
    
    if ($action === 'translate_emoji') {
        $emoji = trim($_POST['emoji']);
        
        // Simple reverse mapping
        $reverse_map = [
            '👁️' => 'I',
            '👤' => 'person',
            '👥' => 'people',
            '❤️' => 'want',
            '🏃' => 'go',
            '👄' => 'eat',
            '✋' => 'do',
            '🧠' => 'think',
            '👂' => 'hear',
            '🏠' => 'home',
            '🍎' => 'food',
            '💧' => 'water',
            '📖' => 'book',
            '💰' => 'money',
            '🛏️' => 'sleep',
            '🚪' => 'door',
            '🌳' => 'tree',
            '🔺' => 'big',
            '🔻' => 'small',
            '🔴' => 'red',
            '🟢' => 'green',
            '🔵' => 'blue',
            '⭐' => 'good',
            '⚡' => 'fast',
            '➡️' => 'to',
            '⬆️' => 'on',
            '⬇️' => 'under',
            '🔲' => '[subject]',
            '🔳' => '[subject end]',
            '🔄' => 'change',
            '📍' => 'now',
            '❓' => 'question',
            '❗' => 'important',
            '➕' => 'and',
            '➖' => 'not',
            '💡' => 'idea',
            '🛡️' => 'protect',
        ];
        
        $emoji_array = explode(' ', $emoji);
        $result = [];
        
        foreach ($emoji_array as $e) {
            if (isset($reverse_map[$e])) {
                $result[] = $reverse_map[$e];
            } else {
                $result[] = $e;
            }
        }
        
        echo json_encode(['english' => implode(' ', $result)]);
        exit;
    }
    
    if ($action === 'submit_phrase') {
        $emoji = trim($_POST['emoji']);
        $english = trim($_POST['english']);
        $ip_hash = hash('sha256', $_SERVER['REMOTE_ADDR'] . 'tinytalk_salt');
        
        $stmt = $db->prepare("INSERT INTO submissions (emoji_sequence, english, ip_hash) VALUES (?, ?, ?)");
        $stmt->bindValue(1, $emoji, SQLITE3_TEXT);
        $stmt->bindValue(2, $english, SQLITE3_TEXT);
        $stmt->bindValue(3, $ip_hash, SQLITE3_TEXT);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Phrase submitted for review!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error submitting phrase']);
        }
        exit;
    }
    
    if ($action === 'get_common_phrases') {
        $complexity = $_POST['complexity'] ?? 1;
        $stmt = $db->prepare("SELECT emoji_sequence, english FROM phrases WHERE complexity <= ? ORDER BY complexity, usage_count DESC LIMIT 20");
        $stmt->bindValue(1, $complexity, SQLITE3_INTEGER);
        $result = $stmt->execute();
        
        $phrases = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $phrases[] = $row;
        }
        
        echo json_encode($phrases);
        exit;
    }
}

// Get random symbol for learning
function getRandomSymbol($db, $category = null) {
    if ($category) {
        $stmt = $db->prepare("SELECT emoji, meaning FROM symbols WHERE category = ? ORDER BY RANDOM() LIMIT 1");
        $stmt->bindValue(1, $category, SQLITE3_TEXT);
    } else {
        $stmt = $db->prepare("SELECT emoji, meaning FROM symbols WHERE complexity <= 2 ORDER BY RANDOM() LIMIT 1");
    }
    $result = $stmt->execute();
    return $result->fetchArray(SQLITE3_ASSOC);
}

// Get symbols by category for reference
function getSymbolsByCategory($db, $category, $limit = 10) {
    $stmt = $db->prepare("SELECT emoji, meaning FROM symbols WHERE category = ? ORDER BY complexity LIMIT ?");
    $stmt->bindValue(1, $category, SQLITE3_TEXT);
    $stmt->bindValue(2, $limit, SQLITE3_INTEGER);
    $result = $stmt->execute();
    
    $symbols = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $symbols[] = $row;
    }
    return $symbols;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>🎭 Tiny-Talk - Universal Emoji Language</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #333;
            line-height: 1.6;
            min-height: 100vh;
            padding: 10px;
            overflow-x: hidden;
        }
        
        .container {
            max-width: 500px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            position: relative;
            overflow: hidden;
        }
        
        .header {
            text-align: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #eee;
        }
        
        h1 {
            font-size: 2.2rem;
            color: #5d4aa2;
            margin-bottom: 5px;
            font-weight: 800;
        }
        
        .tagline {
            color: #666;
            font-size: 1rem;
            font-weight: 500;
        }
        
        .tabs {
            display: flex;
            background: #f0f0f0;
            border-radius: 12px;
            padding: 4px;
            margin-bottom: 25px;
        }
        
        .tab {
            flex: 1;
            text-align: center;
            padding: 14px 5px;
            background: none;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            color: #666;
            cursor: pointer;
            transition: all 0.3s ease;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .tab.active {
            background: white;
            color: #5d4aa2;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .tab-content {
            display: none;
            animation: fadeIn 0.3s ease;
        }
        
        .tab-content.active {
            display: block;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .card {
            background: white;
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
            border: 1px solid #eee;
        }
        
        h2 {
            color: #5d4aa2;
            margin-bottom: 15px;
            font-size: 1.4rem;
            font-weight: 700;
        }
        
        h3 {
            color: #666;
            margin-bottom: 10px;
            font-size: 1.1rem;
            font-weight: 600;
        }
        
        textarea, input[type="text"] {
            width: 100%;
            padding: 16px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 1.1rem;
            resize: vertical;
            margin-bottom: 15px;
            transition: border-color 0.3s ease;
            font-family: inherit;
        }
        
        textarea:focus, input[type="text"]:focus {
            outline: none;
            border-color: #5d4aa2;
        }
        
        .emoji-display {
            background: #f8f9ff;
            border: 2px dashed #d0d5ff;
            border-radius: 12px;
            padding: 20px;
            margin: 15px 0;
            text-align: center;
            font-size: 2.5rem;
            line-height: 1.4;
            min-height: 100px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
            gap: 5px;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        .emoji-display.small {
            font-size: 2rem;
            padding: 15px;
            min-height: 80px;
        }
        
        .button {
            background: linear-gradient(135deg, #5d4aa2 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 16px 24px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            margin: 10px 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .button:hover, .button:active {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(93, 74, 162, 0.3);
        }
        
        .button.secondary {
            background: #f0f0f0;
            color: #666;
        }
        
        .button.small {
            padding: 10px 16px;
            font-size: 0.9rem;
            margin: 5px;
            display: inline-flex;
            width: auto;
        }
        
        .symbol-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
            gap: 12px;
            margin: 15px 0;
        }
        
        .symbol-item {
            background: #f8f9ff;
            border-radius: 10px;
            padding: 12px 8px;
            text-align: center;
            border: 1px solid #e0e5ff;
            transition: transform 0.2s ease;
        }
        
        .symbol-item:active {
            transform: scale(0.95);
        }
        
        .symbol-emoji {
            font-size: 1.8rem;
            margin-bottom: 5px;
        }
        
        .symbol-meaning {
            font-size: 0.8rem;
            color: #666;
            line-height: 1.2;
        }
        
        .example {
            background: #f0f7ff;
            border-radius: 10px;
            padding: 15px;
            margin: 10px 0;
            border-left: 4px solid #5d4aa2;
        }
        
        .example-emoji {
            font-size: 1.8rem;
            margin-bottom: 8px;
            text-align: center;
        }
        
        .example-text {
            font-size: 1rem;
            color: #333;
            text-align: center;
            font-weight: 500;
        }
        
        .complexity-badge {
            display: inline-block;
            background: #e0e5ff;
            color: #5d4aa2;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-left: 10px;
        }
        
        .emoji-keyboard {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 8px;
            margin: 15px 0;
            max-height: 300px;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        .emoji-key {
            background: white;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 12px;
            font-size: 1.5rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .emoji-key:active {
            background: #f0f0f0;
            transform: scale(0.9);
        }
        
        .submit-form {
            display: none;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            color: #888;
            font-size: 0.9rem;
        }
        
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #5d4aa2;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        @media (max-width: 380px) {
            .container {
                padding: 15px;
            }
            
            h1 {
                font-size: 1.8rem;
            }
            
            .tab {
                font-size: 0.9rem;
                padding: 12px 5px;
            }
            
            .emoji-display {
                font-size: 2rem;
                padding: 15px;
            }
            
            .symbol-grid {
                grid-template-columns: repeat(auto-fill, minmax(70px, 1fr));
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎭 Tiny-Talk</h1>
            <div class="tagline">Universal visual language with emoji</div>
        </div>
        
        <div class="tabs">
            <button class="tab active" data-tab="translate">Translate</button>
            <button class="tab" data-tab="learn">Learn</button>
            <button class="tab" data-tab="build">Build</button>
            <button class="tab" data-tab="community">Community</button>
        </div>
        
        <!-- TRANSLATE TAB -->
        <div id="translate" class="tab-content active">
            <div class="card">
                <h2>🔤 Text to Tiny-Talk</h2>
                <textarea id="textInput" placeholder="Type English sentence... (e.g., I want food)" rows="2">I want food</textarea>
                <button class="button" onclick="translateText()">
                    <span>→ Convert →</span>
                </button>
                
                <div class="emoji-display" id="textToEmojiOutput">
                    👁️ ❤️ ⬇️ 🍎
                </div>
                
                <h2>🎭 Tiny-Talk to Text</h2>
                <input type="text" id="emojiInput" placeholder="Paste emoji sequence..." value="👁️ ❤️ ⬇️ 🍎">
                <button class="button secondary" onclick="translateEmoji()">
                    <span>← Convert ←</span>
                </button>
                
                <div class="emoji-display small" id="emojiToTextOutput">
                    I want food
                </div>
            </div>
            
            <div class="card">
                <h3>Quick Examples</h3>
                <div id="quickExamples">
                    <!-- Will be populated by JS -->
                </div>
            </div>
        </div>
        
        <!-- LEARN TAB -->
        <div id="learn" class="tab-content">
            <div class="card">
                <h2>📚 Learn Tiny-Talk</h2>
                <p>Start with these basic symbols:</p>
                
                <h3>People & Actions</h3>
                <div class="symbol-grid">
                    <?php
                    $symbols = getSymbolsByCategory($db, 'person', 6);
                    foreach ($symbols as $symbol): ?>
                    <div class="symbol-item">
                        <div class="symbol-emoji"><?= htmlspecialchars($symbol['emoji']) ?></div>
                        <div class="symbol-meaning"><?= htmlspecialchars($symbol['meaning']) ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <h3>Objects & Things</h3>
                <div class="symbol-grid">
                    <?php
                    $symbols = getSymbolsByCategory($db, 'object', 6);
                    foreach ($symbols as $symbol): ?>
                    <div class="symbol-item">
                        <div class="symbol-emoji"><?= htmlspecialchars($symbol['emoji']) ?></div>
                        <div class="symbol-meaning"><?= htmlspecialchars($symbol['meaning']) ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <h3>Grammar & Position</h3>
                <div class="symbol-grid">
                    <?php
                    $symbols = getSymbolsByCategory($db, 'grammar', 6);
                    foreach ($symbols as $symbol): ?>
                    <div class="symbol-item">
                        <div class="symbol-emoji"><?= htmlspecialchars($symbol['emoji']) ?></div>
                        <div class="symbol-meaning"><?= htmlspecialchars($symbol['meaning']) ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="card">
                <h2>✨ Practice Sentences</h2>
                <div id="practiceSentences">
                    <!-- Will be populated by JS -->
                </div>
                <button class="button secondary" onclick="loadMoreSentences()">
                    <span>Load More Examples</span>
                </button>
            </div>
        </div>
        
        <!-- BUILD TAB -->
        <div id="build" class="tab-content">
            <div class="card">
                <h2>🔨 Build Sentences</h2>
                <div class="emoji-display" id="builderOutput"></div>
                
                <h3>Emoji Keyboard</h3>
                <div class="emoji-keyboard" id="emojiKeyboard">
                    <!-- Populated by JS -->
                </div>
                
                <div style="text-align: center; margin: 15px 0;">
                    <button class="button small secondary" onclick="clearBuilder()">Clear</button>
                    <button class="button small secondary" onclick="removeLastEmoji()">Remove Last</button>
                    <button class="button small" onclick="translateBuilder()">Translate This</button>
                </div>
                
                <div id="builderTranslation" class="example" style="display: none;">
                    <div class="example-text" id="builderTranslationText"></div>
                </div>
            </div>
            
            <div class="card">
                <h2>💡 Submit Your Creation</h2>
                <div class="submit-form" id="submitForm">
                    <input type="text" id="submitEmoji" placeholder="Your emoji sequence..." readonly>
                    <input type="text" id="submitEnglish" placeholder="What does it mean in English?">
                    <button class="button" onclick="submitPhrase()">
                        <span>Submit to Community</span>
                    </button>
                    <div id="submitResult" style="margin-top: 10px; text-align: center;"></div>
                </div>
                <p style="text-align: center; color: #666; margin-top: 15px;">
                    Build a sentence above, then submit it here!
                </p>
            </div>
        </div>
        
        <!-- COMMUNITY TAB -->
        <div id="community" class="tab-content">
            <div class="card">
                <h2>🌍 Community Phrases</h2>
                <div id="communityPhrases">
                    <p style="text-align: center; color: #888; padding: 20px;">
                        Loading community submissions...
                    </p>
                </div>
                
                <div style="text-align: center; margin-top: 20px;">
                    <button class="button small secondary" onclick="loadCommunityPhrases(1)">Beginner</button>
                    <button class="button small secondary" onclick="loadCommunityPhrases(2)">Intermediate</button>
                    <button class="button small secondary" onclick="loadCommunityPhrases(3)">Advanced</button>
                </div>
            </div>
            
            <div class="card">
                <h2>📈 How It Works</h2>
                <div class="example">
                    <div class="example-emoji">👁️ ❤️ ⬇️ 🍎</div>
                    <div class="example-text">
                        <strong>I want food</strong><br>
                        👁️ = I, ❤️ = want, ⬇️ = action-on, 🍎 = food
                    </div>
                </div>
                
                <div class="example">
                    <div class="example-emoji">👤 ➡️ 👄 ⬇️ 🍎</div>
                    <div class="example-text">
                        <strong>Person eats food</strong><br>
                        👤 = person, ➡️ = does action, 👄 = eat, ⬇️ = action-on, 🍎 = food
                    </div>
                </div>
            </div>
        </div>
        
        <div class="footer">
            <p>🎭 Tiny-Talk v1.0 | Universal Emoji Language System</p>
            <p style="font-size: 0.8rem; margin-top: 5px;">
                Works on all devices • No sign-up required • Open to contributions
            </p>
        </div>
    </div>
    
    <script>
    // Current state
    let currentBuilderSequence = [];
    let currentComplexity = 1;
    
    // Tab switching
    document.querySelectorAll('.tab').forEach(tab => {
        tab.addEventListener('click', function() {
            const tabId = this.dataset.tab;
            
            // Update active tab
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            // Show active content
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            document.getElementById(tabId).classList.add('active');
            
            // Load content for tab if needed
            if (tabId === 'learn') {
                loadPracticeSentences();
            } else if (tabId === 'community') {
                loadCommunityPhrases(1);
            } else if (tabId === 'build') {
                initEmojiKeyboard();
            }
        });
    });
    
    // Initialize emoji keyboard for builder
    function initEmojiKeyboard() {
        const keyboard = document.getElementById('emojiKeyboard');
        if (keyboard.innerHTML) return; // Already initialized
        
        // Get all emojis from database via AJAX
        fetch('', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'action=get_common_phrases&complexity=1'
        })
        .then(response => response.json())
        .then(data => {
            // Extract unique emojis from phrases
            let emojis = new Set();
            data.forEach(phrase => {
                phrase.emoji_sequence.split(' ').forEach(emoji => {
                    if (emoji.trim()) emojis.add(emoji);
                });
            });
            
            // Add common emojis if we don't have enough
            const commonEmojis = ['👁️', '👤', '❤️', '🏃', '👄', '🏠', '🍎', '💧', '📖', '➡️', '⬆️', '⬇️', '🔺', '🔻', '🔴', '🟢', '⭐', '⚡', '❓', '❗'];
            commonEmojis.forEach(emoji => emojis.add(emoji));
            
            // Create keyboard buttons
            keyboard.innerHTML = '';
            Array.from(emojis).forEach(emoji => {
                const button = document.createElement('div');
                button.className = 'emoji-key';
                button.textContent = emoji;
                button.onclick = () => addEmojiToBuilder(emoji);
                keyboard.appendChild(button);
            });
        });
    }
    
    // Builder functions
    function addEmojiToBuilder(emoji) {
        currentBuilderSequence.push(emoji);
        updateBuilderDisplay();
    }
    
    function clearBuilder() {
        currentBuilderSequence = [];
        updateBuilderDisplay();
        document.getElementById('builderTranslation').style.display = 'none';
    }
    
    function removeLastEmoji() {
        if (currentBuilderSequence.length > 0) {
            currentBuilderSequence.pop();
            updateBuilderDisplay();
        }
    }
    
    function updateBuilderDisplay() {
        const output = document.getElementById('builderOutput');
        output.textContent = currentBuilderSequence.join(' ') || 'Build your sentence here...';
        document.getElementById('submitEmoji').value = currentBuilderSequence.join(' ');
        
        // Show/hide submit form
        document.getElementById('submitForm').style.display = 
            currentBuilderSequence.length > 0 ? 'block' : 'none';
    }
    
    function translateBuilder() {
        const sequence = currentBuilderSequence.join(' ');
        if (!sequence) return;
        
        translateEmojiSequence(sequence);
    }
    
    // Translation functions
    async function translateText() {
        const text = document.getElementById('textInput').value.trim();
        if (!text) return;
        
        const button = event.target;
        const originalText = button.innerHTML;
        button.innerHTML = '<div class="loading"></div>';
        
        try {
            const response = await fetch('', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=translate_text&text=${encodeURIComponent(text)}`
            });
            
            const data = await response.json();
            document.getElementById('textToEmojiOutput').textContent = data.emoji || 'No translation found';
            
            // Also update the reverse translation field
            document.getElementById('emojiInput').value = data.emoji || '';
            if (data.emoji) {
                translateEmojiSequence(data.emoji, 'emojiToTextOutput');
            }
        } catch (error) {
            console.error('Translation error:', error);
            document.getElementById('textToEmojiOutput').textContent = 'Error translating';
        }
        
        button.innerHTML = originalText;
    }
    
    async function translateEmoji() {
        const emoji = document.getElementById('emojiInput').value.trim();
        if (!emoji) return;
        
        translateEmojiSequence(emoji, 'emojiToTextOutput');
    }
    
    async function translateEmojiSequence(emojiSequence, outputId = 'builderTranslationText') {
        const response = await fetch('', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `action=translate_emoji&emoji=${encodeURIComponent(emojiSequence)}`
        });
        
        const data = await response.json();
        const outputElement = document.getElementById(outputId);
        
        if (outputId === 'builderTranslationText') {
            const container = document.getElementById('builderTranslation');
            outputElement.textContent = data.english || 'Unknown meaning';
            container.style.display = 'block';
        } else {
            outputElement.textContent = data.english || 'No translation found';
        }
    }
    
    // Learning functions
    async function loadPracticeSentences() {
        const container = document.getElementById('practiceSentences');
        container.innerHTML = '<p style="text-align: center; color: #888;">Loading...</p>';
        
        try {
            const response = await fetch('', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=get_common_phrases&complexity=${currentComplexity}`
            });
            
            const phrases = await response.json();
            
            if (phrases.length === 0) {
                container.innerHTML = '<p style="text-align: center; color: #888;">No phrases found</p>';
                return;
            }
            
            let html = '';
            phrases.forEach(phrase => {
                html += `
                <div class="example">
                    <div class="example-emoji">${phrase.emoji_sequence}</div>
                    <div class="example-text">${phrase.english}</div>
                </div>`;
            });
            
            container.innerHTML = html;
        } catch (error) {
            container.innerHTML = '<p style="text-align: center; color: #ff4444;">Error loading phrases</p>';
        }
    }
    
    function loadMoreSentences() {
        currentComplexity = Math.min(3, currentComplexity + 1);
        loadPracticeSentences();
    }
    
    // Community functions
    async function loadCommunityPhrases(complexity) {
        const container = document.getElementById('communityPhrases');
        container.innerHTML = '<p style="text-align: center; color: #888;">Loading...</p>';
        
        try {
            const response = await fetch('', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=get_common_phrases&complexity=${complexity}`
            });
            
            const phrases = await response.json();
            
            if (phrases.length === 0) {
                container.innerHTML = '<p style="text-align: center; color: #888;">No community phrases yet. Be the first!</p>';
                return;
            }
            
            let html = '';
            phrases.forEach(phrase => {
                html += `
                <div class="example">
                    <div class="example-emoji">${phrase.emoji_sequence}</div>
                    <div class="example-text">${phrase.english}</div>
                </div>`;
            });
            
            container.innerHTML = html;
        } catch (error) {
            container.innerHTML = '<p style="text-align: center; color: #ff4444;">Error loading community phrases</p>';
        }
    }
    
    async function submitPhrase() {
        const emoji = document.getElementById('submitEmoji').value.trim();
        const english = document.getElementById('submitEnglish').value.trim();
        
        if (!emoji || !english) {
            alert('Please provide both emoji sequence and English meaning');
            return;
        }
        
        const button = event.target;
        const originalText = button.innerHTML;
        button.innerHTML = '<div class="loading"></div>';
        
        try {
            const response = await fetch('', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=submit_phrase&emoji=${encodeURIComponent(emoji)}&english=${encodeURIComponent(english)}`
            });
            
            const data = await response.json();
            
            document.getElementById('submitResult').innerHTML = `
                <div style="color: ${data.success ? '#4CAF50' : '#ff4444'}; font-weight: 600;">
                    ${data.message}
                </div>
            `;
            
            if (data.success) {
                document.getElementById('submitEnglish').value = '';
                currentBuilderSequence = [];
                updateBuilderDisplay();
                
                // Reload community phrases
                setTimeout(() => loadCommunityPhrases(1), 1000);
            }
        } catch (error) {
            document.getElementById('submitResult').innerHTML = `
                <div style="color: #ff4444; font-weight: 600;">
                    Network error. Please try again.
                </div>
            `;
        }
        
        button.innerHTML = originalText;
    }
    
    // Load quick examples on page load
    window.addEventListener('DOMContentLoaded', function() {
        // Load quick examples
        const examples = [
            ['👁️ ❤️ 🍎', 'I want food'],
            ['👤 ➡️ 👄 ⬇️ 🍎', 'Person eats food'],
            ['🏠 🔺', 'Big house'],
            ['👁️ 🏃 ➡️ 🏠', 'I go home'],
            ['🍎 🔴', 'Red apple']
        ];
        
        let html = '';
        examples.forEach(example => {
            html += `
            <div class="example" style="cursor: pointer;" onclick="document.getElementById('textInput').value='${example[1]}';translateText();">
                <div class="example-emoji">${example[0]}</div>
                <div class="example-text">${example[1]}</div>
            </div>`;
        });
        
        document.getElementById('quickExamples').innerHTML = html;
        
        // Initial translations
        translateText();
        translateEmoji();
        loadPracticeSentences();
    });
    
    // Update builder display on tab switch to build
    document.querySelector('[data-tab="build"]').addEventListener('click', function() {
        setTimeout(updateBuilderDisplay, 100);
    });
    </script>
</body>
</html>

