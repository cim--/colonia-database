# Colonia BGS Database

Database for recording and displaying information on the BGS in Colonia

## Installation

Clone the repository, then run `sh build.sh` (You can also run this
script after updating the repository)

You will also need to run at least some of the database seeds to get
started - StateSeeder, StationclassSeeder and FacilitySeeder are
essential. The others will set up some (old) initial systems for the
region.

Point your web document root at the /public/ folder.

Set cron to run `php artisan cdb:history` at least daily.

## License

GNU General Public License v3 or later - see License.txt

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.

## Use elsewhere

Using outside of Colonia will require a number of modifications, at
least some of which are described in forking.md