const fs = require('fs');
const packageJson = JSON.parse(fs.readFileSync('package.json', 'utf8'));
const version = packageJson.version;

const versionJsContent = `const APP_VERSION = '${version}';\n`;
fs.writeFileSync('assets/js/version.js', versionJsContent);

console.log(`Archivo version.js generado con la versión: ${version}`);