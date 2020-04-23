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

const exec = require('child_process').execSync;

const jsSourcesModified = () => {
  const lastBuildCommit = exec('git log -1 --pretty="format:%H" js/dist/*bundle*js*').toString();
  const output = exec(`git diff --name-only ${lastBuildCommit} HEAD js/src`).toString();
  return !!output && output.split('\n').length > 0;
};

const cssSourcesModified = () => {
  const lastBuildCommit = exec('git log -1 --pretty="format:%H" css/dist/*.css*').toString();
  const output = exec(`git diff --name-only ${lastBuildCommit} HEAD css/src`).toString();
  return !!output && output.split('\n').length > 0;
};

if (jsSourcesModified() || cssSourcesModified()) {
  console.log('\nPlease update and commit distribution bundle first!\nYou may still push using --no-verify option.\n');
  process.exitCode = 1;
}
