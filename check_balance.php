<?php

function checkBladeBalance($filePath) {
    echo "=== Checking: $filePath ===\n";
    $content = file_get_contents($filePath);
    
    // Strip blade comments {{-- ... --}}
    $content = preg_replace('/\{\{--.*?--\}\}/s', '', $content);
    // Strip php tags <?php ... ? > and @php ... @endphp
    $content = preg_replace('/<\?php.*?\?>/s', '', $content);
    $content = preg_replace('/@php.*?@endphp/s', '', $content);
    
    // Let's count <div> and </div>
    preg_match_all('/<div[\s>]/', $content, $mOpen);
    preg_match_all('/<\/div>/', $content, $mClose);
    $openCount = count($mOpen[0]);
    $closeCount = count($mClose[0]);
    echo "<div> open: $openCount, close: $closeCount, diff: " . ($openCount - $closeCount) . "\n";
    
    // Let's count <form> and </form>
    preg_match_all('/<form[\s>]/', $content, $mOpen);
    preg_match_all('/<\/form>/', $content, $mClose);
    echo "<form> open: " . count($mOpen[0]) . ", close: " . count($mClose[0]) . "\n";
    
    // Let's count <button> and </button>
    preg_match_all('/<button[\s>]/', $content, $mOpen);
    preg_match_all('/<\/button>/', $content, $mClose);
    echo "<button> open: " . count($mOpen[0]) . ", close: " . count($mClose[0]) . "\n";
}

checkBladeBalance('c:/laragon/www/GENIE/resources/views/core/components/workflow_pos_stepper.blade.php');
checkBladeBalance('c:/laragon/www/GENIE/resources/views/core/components/project_pic_assignment_card.blade.php');
checkBladeBalance('c:/laragon/www/GENIE/resources/views/core/document_show.blade.php');
