import fs from 'fs';
import path from 'path';

const folders = ['Categories', 'Products', 'StockIns', 'Savings', 'Teachers', 'Students', 'Transactions', 'Vouchers', 'Reports'];
const sourceBase = 'resources/js/Pages/Admin';
const targetBase = 'resources/js/Pages/Kasir';

// List of route prefixes to check
const routePrefixes = [
    'categories.', 'products.', 'stock-ins.', 'savings.',
    'transactions.', 'vouchers.', 'students.', 'teachers.',
    'reports.', 'pos.'
];

function processDirectory(srcPath, targetPath) {
    if (!fs.existsSync(targetPath)) {
        fs.mkdirSync(targetPath, { recursive: true });
    }
    const entries = fs.readdirSync(srcPath, { withFileTypes: true });
    for (let entry of entries) {
        const srcFile = path.join(srcPath, entry.name);
        const targetFile = path.join(targetPath, entry.name);

        if (entry.isDirectory()) {
            processDirectory(srcFile, targetFile);
        } else if (entry.isFile()) {
            if (srcFile.endsWith('.vue')) {
                let content = fs.readFileSync(srcFile, 'utf8');

                // Replace route('...') with route('kasir...')
                content = content.replace(/route\s*\(\s*['"]([^'"]+)['"]/g, (match, routeName) => {
                    let shouldPrefix = false;
                    for (let prefix of routePrefixes) {
                        if (routeName.startsWith(prefix)) {
                            shouldPrefix = true;
                            break;
                        }
                    }

                    if (routeName.startsWith('kasir.')) {
                        shouldPrefix = false;
                    }

                    if (shouldPrefix) {
                        return `route('kasir.${routeName}'`;
                    }
                    return match;
                });

                fs.writeFileSync(targetFile, content, 'utf8');
            } else {
                fs.copyFileSync(srcFile, targetFile);
            }
        }
    }
}

folders.forEach(folder => {
    console.log("Copying and processing", folder);
    const srcDir = path.join(sourceBase, folder);
    const targetDir = path.join(targetBase, folder);
    if (fs.existsSync(srcDir)) {
        processDirectory(srcDir, targetDir);
    } else {
        console.warn(`Source folder does not exist: ${srcDir}`);
    }
});

console.log("Migration complete!");
