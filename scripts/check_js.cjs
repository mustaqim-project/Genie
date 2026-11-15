const fs = require('fs');

const content = fs.readFileSync('resources/views/core/document_index.blade.php', 'utf8');
const scriptMatches = content.match(/<script[\s\S]*?<\/script>/gi);

console.log('Total script tags:', scriptMatches ? scriptMatches.length : 0);

if (scriptMatches) {
    scriptMatches.forEach((s, i) => {
        let code = s.replace(/<script[^>]*>/i, '').replace(/<\/script>/i, '');
        // replace blade directives
        code = code.replace(/\{\{[^}]*\}\}/g, '"dummy_val"');
        code = code.replace(/@json\([^)]*\)/g, '{}');
        code = code.replace(/@csrf/g, '');
        code = code.replace(/@\w+(\([^)]*\))?/g, '');

        try {
            new Function(code);
            console.log(`Script ${i}: VALID JS`);
        } catch (e) {
            console.log(`Script ${i}: SYNTAX ERROR -> ${e.message}`);
            // Find line
            const lines = code.split('\n');
            console.log('First 20 lines of failed script:');
            console.log(lines.slice(0, 20).join('\n'));
        }
    });
}
