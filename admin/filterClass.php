<?php
/**
 * A class from StackOVerflow to facilitate ignoring test site directories
 * when using the RecursiveIteratorIterator.
 * https://stackoverflow.com/questions/15369291/how-to-ignore-directories-using-recursiveiteratoriterator
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
/**
 * A class to facilitate disregarding directories in a recursive search
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
class DirFilter extends RecursiveFilterIterator
{
    protected $exclude;
    /**
     * Class constructor:
     * 
     * @param RecursiveDirecotryIterator $iterator iterator used to scan directories
     * @param array                      $exclude  directories to skip over
     * 
     * @return null
     */
    public function __construct($iterator, array $exclude)
    {
        parent::__construct($iterator);
        $this->exclude = $exclude;
    }
    /**
     * Function to define whether directory is to be accepted for scanning
     * 
     * @return boolean
     */
    public function accept()
    {
        return !($this->isDir() && in_array($this->getFilename(), $this->exclude));
    }
    /**
     * List to find all the excluded dir's children
     * 
     * @return DirFilter
     */
    public function getChildren()
    {
        return new DirFilter(
            $this->getInnerIterator()->getChildren(), $this->exclude
        );
    }
}
