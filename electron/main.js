"use strict";

const isOSX = process.platform === 'darwin';
const isDevMode = process.env.NODE_ENV === 'development';

const electron = require('electron');
const webContents = electron.webContents;
const path = require('path');
const fs = require('fs');

const app = electron.app;
const BrowserWindow = electron.BrowserWindow;
let mainWindow = null;
let mainWebContents = null;


// --> For events/updates
app.commandLine.appendSwitch ("disable-http-cache");



let pluginName
switch (process.platform) {
  case 'win32':
    pluginName = '/plugins/pepflashplayer64_23_0_0_162.dll'
    break
    // if you want the 32-bit version instead, then uncomment the following
    // pluginName = 'plugins/pepflashplayer32_23_0_0_162.dll'
  case 'darwin':
    pluginName = '/plugins/PepperFlashPlayer.plugin'
    break
  case 'linux':
    pluginName = '/plugins/libpepflashplayer.so'
    break
}
app.commandLine.appendSwitch('ppapi-flash-path', path.join(__dirname, pluginName))


app.commandLine.appendSwitch('ppapi-flash-version', '17.0.0.169')

app.whenReady().then(() => {
  const win = new BrowserWindow({
    width: 800,
    height: 600,
    webPreferences: {
      plugins: true
    }
  })
  //win.webContents.openDevTools() use this if you want to load with dev tools
  win.loadURL(`http://localhost`)
  if (isDevMode) win.webContents.openDevTools();
  win.removeMenu();
  

})



