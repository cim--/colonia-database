# Using for another region

You can modify this to track another region, but you'll have to make
quite a few adjustments and will probably want to make several
more. Knowledge of PHP (and the Laravel framework), JS and CSS will be
necessary.

* References to Colonia throughout the views will need changing
* The `coloniaCoordinates` function in `app/Util.php` will need updating to translate to your 'home' coordinates instead.
* You may need to change the map `scaleFactor` in `resources/assets/js/map.js`
* A lot of the front page will need re-making
* Several things will need adding to the database seeders (e.g. there are economy, government, station class, system facility etc. entries which are not included because they're not at Colonia) - some of these will need new icons, CSS classes, and colour schemes
* A superpower allegiance flag for factions - and suitable display on the views and map - is likely to be required. Reserves status for systems might be useful too.
* Settlement phase is probably useless to you as-is, but maybe you can repurpose it as some other 'category', or just leave it out of the views.
* If you want to do any BGS strategy in this app - as opposed to just recording what's happening - you'll need to write all of that. You'll probably want to significantly upgrade the user model and restrictions in that case, too.
* As you're probably not going to be doing a comprehensive record of everything that might be involved, you'll probably want a "None: war elsewhere" and "None: election elsewhere" BGS state adding, for factions which are partly on and partly off the map.
* You'll probably find the distinction between "catalogue" and "name" for systems gets in the way more than it helps.

If you try it and find other things - there will be some! - feel free
to put a pull request on this file with your notes.

## Useful links

* Laravel: https://laravel.com/
* Chart.js: http://www.chartjs.org/
* Fabric: http://fabricjs.com/
* Datatables: https://datatables.net/
