/*
 * μlogger
 *
 * Copyright(C) 2020 Bartek Fabiszewski (www.fabiszewski.net)
 *
 * This is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, see <http://www.gnu.org/licenses/>.
 */

const fs = require('fs');
const crypto = require('crypto');
const exec = require('child_process').execSync;

const getBundleHash = () => {
  const distDir = 'js/dist/';
  const hash = crypto.createHash('sha1');
  fs.readdirSync(distDir)
    .filter(path => path.endsWith('.js'))
    .sort()
    .forEach(path => {
      hash.update(fs.readFileSync(distDir + path));
    });
  return hash.digest('hex');
};

const main = () => {
  const bundleHash = getBundleHash();
  try {
    exec('npm run build');
    if (getBundleHash() !== bundleHash) {
      exec('git add js/dist/*');
    }
  } catch (e) {
    console.log('Warning: build failed!');
  }
};

main();
