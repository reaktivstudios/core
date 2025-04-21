const fs = require('fs-extra');
const path = require('path');

const sourceDir = path.join(__dirname, './inc'); // Root directory of the repository
const docsDir = path.join(sourceDir, '../docs/guides'); // Destination directory for markdown files
const sidebarFile = path.join(sourceDir, '../docs/_sidebar.md'); // Docsify sidebar file


async function copyFile( file, filePath ) {
	await fs.copy(filePath, path.join(docsDir, file));
	await updateDocsifySidebar(file);
	console.log(`Copied: ${filePath} to ${docsDir}`);
}


async function copyMarkdownFiles() {
    try {
        const files = await fs.readdir(sourceDir);

        for (const file of files) {
			console.log( file );
			if (file === 'docs' || file === 'node_modules') {
				continue;
			}

            const filePath = path.join(sourceDir, file);
            const stat = await fs.stat(filePath);

            if (stat.isDirectory()) {
                await copyMarkdownFilesFromDir(filePath);
            } else if (path.extname(file) === '.md') {
				copyFile( file, filePath );
            }
        }
    } catch (error) {
        console.error('Error copying markdown files:', error);
    }
}

async function copyMarkdownFilesFromDir(dir) {
    const files = await fs.readdir(dir);

    for (const file of files) {
        const filePath = path.join(dir, file);
        const stat = await fs.stat(filePath);

        if (stat.isDirectory()) {
            await copyMarkdownFilesFromDir(filePath);
        } else if (path.extname(file) === '.md') {
           	copyFile( file, filePath );
        }
    }
}

async function updateDocsifySidebar(file) {
    try {
        const relativePath = `./guides/${file}`;
        const sidebarContent = await fs.readFile(sidebarFile, 'utf8');
        const lines = sidebarContent.split('\n');

        // Find the "Guides" section
        const guidesIndex = lines.findIndex(line => line.trim() === '- Guides');
        if (guidesIndex === -1) {
            console.error('Could not find "Guides" section in the sidebar.');
            return;
        }

        // Check if the file is already listed
        const alreadyListed = lines.some(line => line.includes(relativePath));
        if (alreadyListed) {
            console.log(`File ${relativePath} is already listed in the sidebar.`);
            return;
        }

        // Add the file under "Guides"
        lines.splice(guidesIndex + 1, 0, `\t- [${path.basename(file, '.md')}](${relativePath})`);
        await fs.writeFile(sidebarFile, lines.join('\n'), 'utf8');

        console.log(`Added ${relativePath} to the sidebar.`);
    } catch (error) {
        console.error('Error updating Docsify sidebar:', error);
    }
}

// Start the copying process
copyMarkdownFiles();
