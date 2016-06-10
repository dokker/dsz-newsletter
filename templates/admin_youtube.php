    <div class="postbox">
      <div class="handlediv" title="Bővebben kikapcsolás/bekapcsolás"><br></div>
      <h3 class="hndle ui-sortable-handle"><span>Youtube video</span></h3>
      <div class="inside youtube">
  		  <p>(Ha nincs URL megadva a hírlevél nem fog Youtube videót tratalmazni.)</p>
	      <p><label for="youtube-url">Video URL:</label> <input type="text" name="youtube-url" class="" value="<?php if (isset($yt_url)) echo $yt_url; ?>" /></p>
	      <p><label for="youtube-title">Video címe:</label> <input type="text" name="youtube-title" class="" value="<?php if (isset($yt_title)) echo $yt_title; ?>" /></p>
      </div>
    </div>
