<?php
/**
 * EBS STEP 2: Displays a parse of the input sentence.
 *
 *
 *
 * @author Liesbeth Augustinus
 * @author Bram Vanroy
 *
 * @see /functions.php  buildEbsMatrix(), isSpam()
 */
session_cache_limiter('private');

session_start();
header('Content-Type:text/html; charset=utf-8');

$currentPage = 'ebs';
$step = 2;

require "../config.php";
require ROOT_PATH."/helpers.php";
require ROOT_PATH."/preparatory-scripts/prep-functions.php";
require ROOT_PATH."/functions.php";
require ROOT_PATH."/preparatory-scripts/alpino-parser.php";
require ROOT_PATH."/preparatory-scripts/simple-dom.php";

$continueConstraints = isset($_POST['input']);

if ($continueConstraints) {
    $treeVisualizer = true;
    $id = session_id();

    $input = $_POST['input'];
}

// Check if $input contains email addresses or website URLs
$isSpam = ($continueConstraints) ? isSpam($input) : false;

require ROOT_PATH."/front-end-includes/head.php";
?>
</head>
<?php flush(); ?>
<?php
require ROOT_PATH."/front-end-includes/header.php";

if ($continueConstraints && !$isSpam) {

    $_SESSION['example'] = $input;

    // Prepare/clean up input to be tokenized in next step
    $tokinput = tokenize($input);
    $_SESSION['sentence'] = $tokinput;
    $parse = alpino($tokinput, $id);
    modifyLemma($parse, $id);
?>

  <p>You find the structure of the tagged and parsed sentence below.
    Tagging indicates <em>word classes</em>, such as <code>n</code> (noun),
    and parsing shows <em>dependencies</em> (or relations),
     such as <code>su</code> (subject) and <em>constituents</em>,
     such as <code>np</code> (noun phrase).
  </p>

  <p>Your input sentence was: <em><?php echo $tokinput; ?></em></p>

  <div id="tree-output">
      <?php include ROOT_PATH."/front-end-includes/tv-wrappers.php"; ?>
  </div>

  <form action="ebs/matrix.php" method="post" enctype="multipart/form-data">
    <p>If the analysis is different from what you expected, you can enter
      <a href="ebs/input.php" title="Example-based search">a new input example</a>.
    </p>
    <?php setContinueNavigation(); ?>
  </form>
<?php
} else {
    if ($isSpam):
        setErrorHeading("spam detected"); ?>
        <p>Your input example contained a hyperlink or email address and is seen as spam. Therefore we will not allow you to continue. </p>
    <?php else:
        setErrorHeading('variables undefined'); ?>
        <p>It seems that you did not enter an input sentence. It is also
        possible that you came to this page directly without first entering an input example.</p>
    <?php endif;
    setPreviousPageMessage($step-1);
}

session_write_close();

require ROOT_PATH."/front-end-includes/footer.php";
if ($continueConstraints) : ?>
  <script>
  $(function() {
      $("#tree-output").treeVisualizer('<?php echo "tmp/$id-pt.xml"; ?>', {
        sentence: '<?php echo $input; ?>'
    });
  });
  </script>
<?php endif;
include ROOT_PATH."/front-end-includes/analytics-tracking.php";
?>
</div>
<script src="<?php echo $home; ?>/js/min/tree-visualizer.min.js"></script>
<script>
$.treeVisualizer('<?php echo "$home/tmp/$id"; ?>-pt.xml', {
    container: "#tree-output"
});
</script>

</body>
</html>
