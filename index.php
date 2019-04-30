<?php
$planetRelativeCoords = [
   'dathomir' => ['x' => 61.4, 'y' => 27.6],
   'dagobah' => ['x' => 30.8, 'y' => 79.7],
   'dantooine' => ['x' => 62.7, 'y' => 21.3],
   'mon-calamari' => ['x' => 80.9, 'y' => 44.6],
   'kamino' => ['x' => 48.1, 'y' => 82.3],
   'apk1' => ['x' => 15, 'y' => 25],
   'lwhekk' => ['x' => 17.3, 'y' => 51],
   'luprora' => ['x' => 20, 'y' => 20],
   'kpibinom3' => ['x' => 25, 'y' => 15],
   'ambria' => ['x' => 57.9, 'y' => 44.2],
];
?>
<!DOCTYPE html>
<html lang="ru">
    <head>
        <link href="/css/main.css" rel="stylesheet">
    </head>
    <body>
        <div id="map">
        </div>
        <script src="/js/jquery.js"></script>
        <script src="/js/main.js"></script>
        <script>
            planetRelativeCoords = <?= json_encode($planetRelativeCoords);?>;
        </script>
    </body>
</html>

