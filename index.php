<?php

class QR {
    protected $code = array();
    protected $version;
    protected $size;
    
    // Quiet zone is 4 pixels around the outside of 0's
    
    public function __construct($version) {
        $this->createBlankCode($version)
             ->addFinderPatterns()
             ->addTimingPattern()
             ->addAlignmentPatterns();
    }
    
    public function createBlankCode($version) {
        $this->version = $version;
        $this->size = 17 + ($version * 4);
        
        $this->code = array_fill(0, $this->size, array_fill(0, $this->size, 0));
        return $this;
    }
    
    public function addFinderPatterns() {
        $finderPattern = array(
            array(1,1,1,1,1,1,1),
            array(1,0,0,0,0,0,1),
            array(1,0,1,1,1,0,1),
            array(1,0,1,1,1,0,1),
            array(1,0,1,1,1,0,1),
            array(1,0,0,0,0,0,1),
            array(1,1,1,1,1,1,1),
        );
        
        $this->addPattern($finderPattern, 0, 0);
        $this->addPattern($finderPattern, $this->size - 7, 0);
        $this->addPattern($finderPattern, 0, $this->size - 7);
        return $this;
    }
    
    public function addTimingPattern() {
        for ($i = 8; $i < $this->size - 8; $i++) {
            $this->code[6][$i] = ($i + 1) % 2;
            $this->code[$i][6] = ($i + 1) % 2;
        }
        
        return $this;
    }
    
    // TODO make this function dynamic
    public function addAlignmentPatterns() {
        $alignmentPattern = array(
            array(1,1,1,1,1),
            array(1,0,0,0,1),
            array(1,0,1,0,1),
            array(1,0,0,0,1),
            array(1,1,1,1,1),
        );
        
        $coordinates = array(6);
        
        if ($this->version > 34) {
            $coordinates[] = ($this->version % 3) * 4 + floor($this->version / 3) * 2;
        }
        
        if ($this->version > 27) {
            $temp = array(26,30,26,30,34,30,34,54,50,54,58,54,58);
            $coordinates[] = $temp[$this->version - 28];
        }
        
        if ($this->version > 20) {
            $temp = array(28,26,30,28,32,30,34,50,54,52,56,60,58,62,78,76,80,84,82,86);
            $coordinates[] = $temp[$this->version - 21];
        }
        
        if ($this->version > 13) {
            $temp = array(26,26,26,30,30,30,34,50,50,54,54,58,58,62,74,78,78,82,86,86,90,102,102,106,110,110,114);
            $coordinates[] = $temp[$this->version - 14];
        }
        
        if ($this->version > 6) {
            $temp = array(22,24,26,28,30,32,34,46,48,50,54,56,58,62,72,74,78,80,84,86,90,98,102,104,108,112,114,118,126,128,132,136,138,142);
            $coordinates[] = $temp[$this->version - 7];
        }
                
        if ($this->version > 1) {
            $coordinates[] = $this->version * 4 + 10;
        }
        
        foreach ($coordinates as $a) {
            foreach ($coordinates as $b) {
                if (($a == 6 && $b == 6)
                        || ($a == 6 && $b == $this->size - 7)
                        || ($a == $this->size - 7 && $b == 6)) {
                    continue;
                }
                
                $this->addPattern($alignmentPattern, $a, $b, true);
            }
        }
        
        return $this;
    }
    
    public function addPattern($pattern, $x, $y, $centered = false) {
        if ($centered) {
            $x -= floor(count($pattern) / 2);
            $y -= floor(count($pattern[0]) / 2);
        }
        
        foreach ($pattern as $x1 => $a) {
            foreach ($a as $y1 => $b) {
                $this->code[$x1 + $x][$y1 + $y] = $b;
            }    
        }
        return $this;
    }
    
    public function printCode() {
        echo '<pre>';
        foreach ($this->code as $a) {
            foreach ($a as $b) {
                echo $b;
            }
            echo '<br />';
        }
            
        echo '</pre>';
    }    
}

$qr = new QR(isset($_GET['v']) ? $_GET['v'] : 1);
$qr->printCode();