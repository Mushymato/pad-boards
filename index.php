<!DOCTYPE html>
<html>
	<head>
        <meta charset='utf-8'/>
        <title>PAD Boards Database @ Blogging Mama</title>
        <link rel="stylesheet" type="text/css" href="boards.css">
        <link rel="stylesheet" type="text/css" href="style.css">
        
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js" type="text/javascript"></script>
        <script src="board_filters.js" type="text/javascript"></script>
        <link rel="stylesheet" href="./colorbox/colorbox.css">
        <script src="./colorbox/jquery.colorbox.js"></script>

		<script>
			$(document).ready(function(){
				$(".board-url").colorbox({iframe:true, width:"80%", height:"90%"});
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

    <div class="header">
        <div class="header-bg">
        </div>
        <div class="content">
            <span class="pad-title">Puzzle & Dragons</span>
            <p class="heading-title"><a href="./">Boards Database</a></p>
            <ul class="navbar">
            	<li><a href="#">About</a></li>
                <li><a href="../">Return to Blog</a></li>
            </ul>
        </div>
    </div>
    
    <section id="display-boards">
        <?php
        function option($value, $text, $check){
            return '<option value="'. $value .'"' . ($check == $value ? ' selected' : ' ') . '>' . $text . '</option>' . PHP_EOL;
        }
        include 'boards_common.php';
        include 'sql_param.php';
        $conn = connect_sql($host, $user, $pass, $schema);
        $size = array_key_exists('board_size', $_GET) ? $_GET['board_size'] : 'm';
        $orb_count = array_key_exists('orb_count', $_GET) ? intval($_GET['orb_count']) : 2;
        $hearts = array_key_exists('hearts', $_GET) ? boolval($_GET['hearts']) : false;
        $boards = select_boards($conn, $size, $orb_count, $hearts);
        ?>
        <form action='' class="grid fset">
            <fieldset>
                <legend>Select Board Type</legend>
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
            <?php echo get_attribute_filters($boards);?>
        </form>
        <?php 
        echo '<div class="boards float size-' . $size . '">' . display_boards($boards) . '</div>';
        ?>
	</section>
    <footer>
        <p class="title">PAD Boards Database</p>
        <p class="bm"><a href="../">@ Blogging Mama</a></p>
        <span class="credits heading">Creator</span><span class="credits">chu2</span><br />
        <span class="credits heading">Data input</span><span class="credits">Umby, InsanityBringer</span>
        <p>&copy; <?php echo date("Y"); ?> chu2. Puzzle & Dragons logo and all related images are registered trademarks or trademarks of GungHo Online Entertainment, Inc.</p>
    </footer>
</body>
</html>