const { app, BrowserWindow, dialog } = require('electron');
const path = require('path');
const { spawn } = require('child_process');
const http = require('http');
const getPort = require('get-port');

let mainWindow = null;
let phpProcess = null;

function phpExecutablePath() {
 const localPhp = path.join(__dirname, 'php', 'php.exe');
  const packagedPhp = path.join(process.resourcesPath, 'php', 'php.exe');
  return app.isPackaged ? packagedPhp : localPhp;
}

function wwwPath() {
  return app.isPackaged ? path.join(process.resourcesPath, 'www') : path.join(__dirname, 'www');
}

function waitForServer(port, timeout = 5000) {
  const start = Date.now();
  return new Promise((resolve, reject) => {
    (function check() {
      const req = http.request({ method: 'GET', host: '127.0.0.1', port, path: '/' }, (res) => {
        resolve();
      });
      req.on('error', () => {
        if (Date.now() - start > timeout) return reject(new Error('timeout'));
        setTimeout(check, 100);
      });
      req.end();
    })();
  });
}

async function createWindow() {
  try {
    const port = await getPort({ port: 8000 });


    const phpPath = phpExecutablePath();
    const www = wwwPath();

    phpProcess = spawn(phpPath, ['-S', `127.0.0.1:${port}`, '-t', www], {
      windowsHide: true,
      stdio: 'ignore'
    });

    phpProcess.on('error', (err) => {
      dialog.showErrorBox('Erro ao iniciar PHP', String(err));
      app.quit();
    });

    await waitForServer(port, 5000);

    mainWindow = new BrowserWindow({
      width: 1000,
      height: 720,
      show: false,
      webPreferences: {
        contextIsolation: true,
        preload: path.join(__dirname, 'preload.js')
      }
    });

    mainWindow.once('ready-to-show', () => mainWindow.show());
    mainWindow.on('closed', () => {
      mainWindow = null;
    });

    const url = `http://127.0.0.1:${port}/`;
    await mainWindow.loadURL(url);

  } catch (err) {
    dialog.showErrorBox('Erro', String(err));
    app.quit();
  }
}

app.on('ready', createWindow);

app.on('before-quit', () => {
  if (phpProcess) {
    try {
      phpProcess.kill();
    } catch (e) {}
  }
});
