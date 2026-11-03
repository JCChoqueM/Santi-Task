import fs from 'fs';
import path from 'path';

function updateIndex(fileDir, fileName, action) {
    const indexPath = path.join(fileDir, '_index.scss');
    const importLine = `@forward '${fileName}';`;

    if (action === 'add') {
        let content = fs.existsSync(indexPath)
            ? fs.readFileSync(indexPath, 'utf8')
            : '';

        if (!content.includes(importLine)) {
            fs.appendFileSync(indexPath, importLine + '\n');
            console.log(`[scss-auto-index] Añadido: ${importLine}`);
        }
    }

    if (action === 'remove') {
        if (!fs.existsSync(indexPath)) return;

        let content = fs.readFileSync(indexPath, 'utf8');
        const updated = content
            .split('\n')
            .filter(line => !line.includes(importLine))
            .join('\n')
            .trim();

        if (updated === '') {
            fs.unlinkSync(indexPath);
            console.log(`[scss-auto-index] Eliminado _index.scss vacío en ${fileDir}`);
        } else {
            fs.writeFileSync(indexPath, updated + '\n');
            console.log(`[scss-auto-index] Eliminado: ${importLine}`);
        }
    }
}

function updateAppScss(scssAbsDir, folderName, action) {
    const appScssPath = path.join(scssAbsDir, 'app.scss');
    const useLine = `@use '${folderName}';`;

    if (action === 'add') {
        let content = fs.existsSync(appScssPath)
            ? fs.readFileSync(appScssPath, 'utf8')
            : '';

        if (!content.includes(useLine)) {
            fs.appendFileSync(appScssPath, useLine + '\n');
            console.log(`[scss-auto-index] app.scss: Añadido: ${useLine}`);
        }
    }

    if (action === 'remove') {
        if (!fs.existsSync(appScssPath)) return;

        let content = fs.readFileSync(appScssPath, 'utf8');
        const updated = content
            .split('\n')
            .filter(line => !line.includes(useLine))
            .join('\n')
            .trim();

        fs.writeFileSync(appScssPath, updated + '\n');
        console.log(`[scss-auto-index] app.scss: Eliminado: ${useLine}`);
    }
}

function watchDir(scssDir) {
    const absDir = path.resolve(process.cwd(), scssDir);

    // Carpetas de primer nivel que ya existen al arrancar
    let knownDirs = new Set(
        fs.existsSync(absDir)
            ? fs.readdirSync(absDir, { withFileTypes: true })
                .filter(d => d.isDirectory())
                .map(d => d.name)
            : []
    );

    fs.watch(absDir, { recursive: true }, (event, filename) => {
        if (!filename) return;

        const filePath = path.join(absDir, filename);
        const isTopLevel = path.dirname(filename) === '.';

        // --- Manejo de carpetas nuevas/eliminadas de primer nivel ---
        if (event === 'rename' && isTopLevel) {
            const name = filename;
            const exists = fs.existsSync(filePath);
            const isDir = exists && fs.statSync(filePath).isDirectory();

            if (exists && isDir && !knownDirs.has(name)) {
                // Carpeta nueva
                knownDirs.add(name);

                const indexPath = path.join(filePath, '_index.scss');
                if (!fs.existsSync(indexPath)) {
                    fs.writeFileSync(indexPath, '');
                    console.log(`[scss-auto-index] Creado _index.scss en ${name}/`);
                }

                updateAppScss(absDir, name, 'add');
            } else if (!exists && knownDirs.has(name)) {
                // Carpeta eliminada
                knownDirs.delete(name);
                updateAppScss(absDir, name, 'remove');
            }
        }

        // --- Manejo de archivos .scss individuales (lógica original) ---
        if (!filename.endsWith('.scss')) return;
        if (path.basename(filename) === '_index.scss') return;

        const fileDir = path.dirname(filePath);
        const fileName = path.basename(filename, '.scss').replace(/^_/, '');

        if (event === 'rename') {
            if (fs.existsSync(filePath)) {
                updateIndex(fileDir, fileName, 'add');
            } else {
                updateIndex(fileDir, fileName, 'remove');
            }
        }
    });
}

export default function scssAutoIndex(scssDir = 'src/scss') {
    return {
        name: 'scss-auto-index',
        configureServer() {
            const absDir = path.resolve(process.cwd(), scssDir);
            console.log(`[scss-auto-index] Watching: ${absDir}`);
            watchDir(scssDir);
        }
    };
}