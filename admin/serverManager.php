<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>jstree basic demos</title>
  <style>
  html { margin:0; padding:0; font-size:62.5%; }
  body { max-width:800px; min-width:300px; margin:0 auto; padding:20px 10px; font-size:14px; font-size:1.4em; }
  h1 { font-size:1.8em; }
  .demo { overflow:auto; border:1px solid silver; min-height:100px; }
  </style>
  <link rel="stylesheet" href="./styles/themes/default/style.min.css" />
</head>
<body>
  <h1>服务器列表</h1>
  <div id="data" class="demo"></div>

  <script src="./scripts/jquery-1.11.2.min.js"></script>
  <script src="./styles/jstree.min.js"></script> 
  <script>
  // html demo
  $('#html').jstree();

  // inline data demo
  $('#data').jstree({
    'core' : {
      'data' : [
        { "text" : "127.0.0.1", "children" : [
            { "text" : "32040451991311000098" },
            { "text" : "32040451991311000099" }
        ]},
        { "text" : "128.0.0.1", "children" : [
            { "text" : "32040451991311000098" },
            { "text" : "32040451991311000099" }
        ]}
      ]
    }
  });
  </script>
</body>
</html>