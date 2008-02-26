<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN"
   "http://www.w3.org/TR/html4/frameset.dtd">
<html>
<head>
<title>Beschreibung des Frameset-Inhalts</title>
</head>
<frameset cols="200,*">
<frame src="menu.php" name="NaviFrame" frameborder="0">
  <frameset rows="100,*">
  <frame src="top.php" name="TopFrame" frameborder="0" noresize scrolling="no">
  <frame src="http://www.mozilla.org/" name="MainFrame" frameborder="0">
  </frameset>

  <noframes>
    <body>
      <h1>Alternativ-Inhalt</h1>
      <p>Wird angezeigt, wenn der Browser keine Frames darstellen kann.</p>
      <p>Mindest-Inhalt sollten eine Kurzbeschreibung und eine Sitemap sein.</p>
    </body>
  </noframes>
</frameset>
</html>