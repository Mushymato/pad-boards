<!DOCTYPE html>
<html>
	<head>
        <meta charset='utf-8'/>
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <title>PAD Boards Database | Blogging Mama</title>
        <link rel="stylesheet" type="text/css" href="boards.css">
        <link rel="stylesheet" type="text/css" href="style.css">
        
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/css/bootstrap.min.css" integrity="sha384-GJzZqFGwb1QTTN6wy59ffF1BuGJpLSa9DkKMp0DgiMDm4iYMj70gZWKYbI706tWS" crossorigin="anonymous">
        
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js" type="text/javascript"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
        <script src="board_filters.js" type="text/javascript"></script>
        
        <link rel="stylesheet" href="./colorbox/colorbox.css">
        <script src="./colorbox/jquery.colorbox.js"></script>

		<script>
			$(document).ready(function(){
				$(".board-url").colorbox({iframe:true, width:"80%", height:"90%"})
				$(".about").colorbox({inline:true, width:"80%"});
			});
		</script>
        <script>
        $(document).ready(function(){
            initializeOrbMap();
            intializeFilters();
            addFilterListeners();
            updateOrbColors();
            updateOrbRadios();
            updateFilters();
        });
        </script>
	</head>
<body>
    <main id="main" class="site-main" role="main">
 
    <nav style="background-color: #117c7c;" class="navbar navbar-expand-lg navbar-dark bg-dark">
    
       <h5 class="my-0 mr-md-auto text-light site-name font-weight-bold"><a class="navbar-brand" href="../boards">PAD Boards Database</a></h5>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarText" aria-controls="navbarText" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarText">
        <ul class="navbar-nav mr-auto">
          <li class="nav-item active">
            <a class="nav-link about" href="#about">About</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="../">Blogging Mama</a>
          </li>
        </ul>
        <span class="navbar-text">
          Optimal boards database.
        </span>
      </div>
    </nav>
		<!-- About -->
		<div style='display:none'>
			<div id='about' style='padding:10px; background:#fff;'>
    			<h2>PAD Boards Database</h2>
        		<p>Optimal boards for all your needs!</p>
                

                    <h2>Credits</h2>
                    <p>
                    <span class="">Creator:</span> chu2<br>
                    <span class="">Data Input &amp; Verification</span>: Miso [<a href="http://misopad.wordpress.com/">Link</a>]<br>
                    <span class="">Helpers:</span>  Umby, InsanityBringer</p>
                
                <h2>Board sources</h2>
                <ul>
                	<li>SetsuPAD [<a href="https://setsupad.wordpress.com/optimal-boards/">Link</a>]</li>
                    <li>Miso [<a href="http://misopad.wordpress.com/">Link</a>]</li>
                    <li>Netete PAD [<a href="#">Link</a>]</li>
                    <li>River</li>
                    <li>goffrie [<a href="https://gist.github.com/goffrie/852ca94a6d2629a1e4422fd774a425a7">GitHub</a>]</li>
                    <li>yaypad</li>
                    
                </ul>
                
			</div>
		</div>
        
    <section>
        <?php
        function option($value, $text, $check){
            return '<option value="'. $value .'"' . ($check == $value ? ' selected' : ' ') . '>' . $text . '</option>' . PHP_EOL;
        }
        include 'boards_common.php';
        include 'sql_param.php';
        $conn = connect_boards_sql($host, $user, $pass, $schema);
        $size = array_key_exists('board_size', $_GET) ? $_GET['board_size'] : 'm';
        $orb_count = array_key_exists('orb_count', $_GET) ? intval($_GET['orb_count']) : 2;
        $hearts = array_key_exists('hearts', $_GET) ? boolval($_GET['hearts']) : false;
        $boards = select_boards($conn, $size, $orb_count, $hearts);
		?>
        <!-- Board type -->
		<div class="container">
			<div class="row">
				<div class="col-md-4">
				<form action=''>
					   <fieldset class="border p-2">
						<legend class="w-auto">Select Board Type</legend>
						<select name="orb_count">
							<?php
								echo option(2, 'Bicolor', $orb_count);
								echo option(3, 'Tricolor', $orb_count);
							?>
						</select>
						<select name="board_size">
							<?php
								echo option('s', '5x4', $size);
								echo option('m', '6x5', $size);
								echo option('l', '7x6', $size);
							?>
						</select>
						<select name="hearts">
							<?php
								echo option('0', 'no hearts', $hearts);
								echo option('1', 'with hearts', $hearts);
							?>
						</select>
						<button type="submit" value="Submit">Submit</button>
					</fieldset>
					</form>
					</div>
				<div class="col-md-8">    
					<form action=''>        
					<?php echo get_filters($boards);?>
					</form>
				</div>
			</div>
		</div>
	</section>
	<section>
        <!-- Board display -->
		<div class="container">
			<div class="row">
					<?php 
					echo '<div class="text-center boards float size-' . $size . '">' . display_boards($boards) . '</div>';
					?>
			</div>
		</div>
	</section>
    </main>
    <footer>
		<div class="container">
			<div class="row">
				<div class="col justify-content-center">
                    <p class="title">PAD Boards Database</p>
                    <p class="bm"><a href="../">@ Blogging Mama</a></p>
                    <span class="credits heading">Creator</span><span class="credits">chu2</span><br />
                    <span class="credits heading">Data Input &amp; Verification</span><span class="credits">Miso [<a href="http://misopad.wordpress.com/" style="color:white">Link</a>]</span><br />
                  
                    <p>&copy; <?php echo date("Y"); ?> chu2. Puzzle & Dragons logo and all related images are registered trademarks or trademarks of GungHo Online Entertainment, Inc.</p>
				</div>
			</div>
		</div>
    </footer>
</body>
</html>