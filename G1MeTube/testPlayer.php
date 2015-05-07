<?php

$name='music';
$typr=1;
$extension='mp3';

?>



<video id="movie" width="640" height="320" preload controls>
  <source src="testStream.php?name=<?php echo $name?>&type=2&extension=webm" type="video/webm; codecs=vp8,vorbis" />
  <source src="testStream.php?name=<?php echo $name?>&type=2&extension=ogv" type="video/ogg; codecs=theora,vorbis" />
  <source src="testStream.php?name=<?php echo $name?>&type=2&extension=mp4" />
  <object width="640" height="320" type="application/x-shockwave-flash"
    data="flowplayer-3.2.1.swf">
    <param name="movie" value="flowplayer-3.2.1.swf" />
    <param name="allowfullscreen" value="true" />
    <param name="flashvars" value="config={'clip': {'url': 'testStream.php?name=<?php echo $name?>&type=2&extension=mp4', 'autoPlay':false, 'autoBuffering':true}}" />
    <p>Download video as <a href="pr.mp4">MP4</a>, <a href="pr6.webm">WebM</a>, or <a href="pr6.ogv">Ogg</a>.</p>
  </object>
</video>
<script>
  var v = document.getElementById("movie");
  v.onclick = function() {
    if (v.paused) {
      v.play();
    } else {
      v.pause();
    }
  };
</script>
