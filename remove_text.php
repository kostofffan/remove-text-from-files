<?php
/**
 * Remove any text from all files under specified directory.
 *
 * The script recursively inspects a folder. All files that are lesser than size threshold
 * and has allowed extension are inspected for a search string (not regexp).
 * If such string is found it is removed from the file.
 * The script does not process files started with "." (dot).
 * Note: the script reads whole file into the memory. Therefore be careful with size threshold.
 *
 * At the end script prints out the number of files with search string found.
 *
 * Usage:
 * ```
 * php remove_text.php
 *      --dir=DIRECTORY_PATH
 *      --search=STRING | --searchFile=FILE_PATH
 *      [--extensions=COMMA_SEPARATED_EXTENSIONS]
 *      [--size=SIZE_THRESHOLD]
 * ```
 *
 * `dir` - path to the directory to process
 *
 * `search` - string to search. May be omitted if searchFile is set
 *
 * `searchFile` - path the file containing search text
 *
 * `extensions` - (optional) list of allowed extensions separated with comma (without spaces)
 *
 * `size` - (optional) size threshold for files. Bigger files won't be processed. Default is 5 Mb.
 *
 * @link https://github.com/kostofffan/remove-text-from-files
 */

/**
 * Class TextRemover
 * Can run through directory structure and find all files with allowed extensions.
 * Then search string is removed from all files found.
 *
 * @author Konstantin Esin <kostofffan@gmail.com>
 */
class TextRemover
{
    /**
     * Name of the directory to inspect
     * @var string
     */
    private $dir;

    /**
     * List of allowed extensions. Other files will be ignored.
     * @var array
     */
    private $allowedExtensions = array();

    /**
     * Files with bigger size will be ignored.
     * @var integer|null
     */
    private $sizeThreshold = 0;

    /**
     * Search string
     * @var string
     */
    private $search;

    /**
     * Counter for processed files
     * @var integer
     */
    public $counter = 0;

    /**
     * Constructor
     *
     * @param string       $search String to remove
     * @param string       $dir    Directory to search the string in
     * @param array        $ext    List of allowed extensions
     * @param integer|null $size   Size threshold
     */
    public function __construct($search, $dir, $ext = array(), $size = null)
    {
        $this->search = trim($search);
        $this->dir = rtrim($dir, '/\\') . '/';
        $this->allowedExtensions = $ext;
        if (is_null($size)) {
            $this->sizeThreshold = 5 * 1024 * 1024;
        } else {
            $this->sizeThreshold = $size;
        }
    }

    /**
     * Start the process.
     *
     * At this point directory and search string should be specified.
     *
     * @return integer Number of files with a search string
     */
    public function run()
    {
        $this->counter = 0;
        $this->processDirectory($this->dir);
        return $this->counter;
    }

    /**
     * Search files in the specified directory and remove text from them.
     *
     * If another directory is found call itself recursively
     *
     * @param string $dir Directory path with slash at the end
     */
    public function processDirectory($dir)
    {
        $dh = opendir($dir);
        if (!$dh) { print "Can't open directory $dir\n"; return; }

        while ($file = readdir($dh)) {
            if ($file{0} == '.') { // skip hidden files and directories
                continue;
            }
            if (is_dir($dir . $file)) {
                $this->processDirectory($dir . $file . '/');
            } else {
                if (filesize($dir . $file) > $this->sizeThreshold) {
                    print "File $dir$file is bigger than threshold set ({$this->sizeThreshold} bytes)\n";
                    continue;
                }
                if (!is_readable($dir . $file) or !is_writeable($dir . $file)) {
                    print "Can't read/write file $dir$file\n";
                    continue;
                }

                $extension = pathinfo($file, PATHINFO_EXTENSION);
                if (empty($this->allowedExtensions) or in_array($extension, $this->allowedExtensions)) {
                    $contents = file_get_contents($dir . $file);
                    if (strpos($contents, $this->search) !== false) {
                        $contents = str_replace($this->search, '', $contents);
                        file_put_contents($dir . $file, $contents);
                        $this->counter++;
                    } else {
                    }

                }
            }
        }
    }

}





// Process input arguments
$_ARG = array();
foreach ($argv as $arg) {
    if (preg_match('/--([^=]+)="?(.*)"?/',$arg,$reg)) {
        $_ARG[$reg[1]] = $reg[2];
    } elseif(preg_match('/-([a-zA-Z0-9])/',$arg,$reg)) {
        $_ARG[$reg[1]] = 'true';
    }
}

if (!isset($_ARG['dir'])) {
    die("Specify directory\n");
}
$dir = $_ARG['dir'];


$search = '';
if (isset($_ARG['search'])) {
    $search = $_ARG['search'];
} elseif (isset($_ARG['searchFile'])) {
    $search = file_get_contents($_ARG['searchFile']);
}

if (!$search) {
    die("Specify search\n");
}

$size = isset($_ARG['size']) ? $_ARG['size'] : null;

$ext = array();
if (isset($_ARG['extensions'])) {
    $ext = explode(',', $_ARG['extensions']);
}
// stop processing input arguments



$tr = new TextRemover($search, $dir, $ext, $size);
$c = $tr->run();

print "Text was removed from $c files\n\n";