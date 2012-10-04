<?php

class Drapeau {

	protected $factor;
	protected $colors;
	protected $zones;
	protected $zones_types = array();

	public function __construct($width) {
		$this->factor = $width / 3;
		array_unshift($this->zones, array(0, 0, 3, 0, 3, 2, 0, 2));
	}

	public function draw() {
		$width = $this->factor * 3;
		$height = $this->factor * 2;
		$image = imagecreatetruecolor($width + 1, $height + 1);

		$black = imagecolorallocate($image, 0, 0, 0);
		$white = imagecolorallocate($image, 255, 255, 255);
		$transparent = imagecolorallocate($image, 255, 254, 255);
		imagecolortransparent($image, $transparent);

		imagefill($image, 0, 0, $transparent);
		foreach($this->zones as $index => $zone) {
			$points = array();
			foreach ($zone as $point) {
				$points[] = $point * $this->factor;
			}
			if (isset($this->colors[$index])) {
				list($r, $v, $b) = $this->colors[$index];
				$color =  imagecolorallocate($image, $r, $v, $b);
				if (isset($this->zones_types[$index]) and $this->zones_types[$index] == "ellipse") {
					list($x, $y, $w, $h) = $points;
					imagefilledellipse($image, $x, $y, $w, $h, $color);
				}
				else {
					imagefilledpolygon($image, $points, count($points) / 2, $color);
				}
			}
			else {
				if (isset($this->zones_types[$index]) and $this->zones_types[$index] == "ellipse") {
					list($x, $y, $w, $h) = $points;
					imagefilledellipse($image, $x, $y, $w, $h, $transparent);
					imageellipse($image, $x, $y, $w, $h, $black);
				}
				else {
					imagefilledpolygon($image, $points, count($points) / 2, $transparent);
					imagepolygon($image, $points, count($points) / 2, $black);
				}
			}
		}

		header('Content-type: image/png');

		imagepng($image);
		imagedestroy($image);
	}

	public function zone($zone_id, $color) {
		$this->colors[$zone_id] = $color;
	}

	public function fill($x, $y, $color) {
		$this->zone($this->get_zone($x, $y), $color);
	}
}

class Drapeau1 extends Drapeau {

	protected $zones = array(
		array(
			0, 0,
			3, 0,
			0, 2,
		),
	);

	public function get_zone($x, $y) {
		if ($y + (2/3)*$x - (2 * $this->factor) > 0) {
			return 0;
		}
		else {
			return 1;
		}
	}
}

class Drapeau2 extends Drapeau {

	protected $zones = array(
		array(
			0.2, 0.2,
			2.8, 0.2,
			2.8, 1.8,
			0.2, 1.8,
		),
	);

	public function get_zone($x, $y) {
		$x = $x / $this->factor;
		$y = $y / $this->factor;
		if ($x < 0.2 or $x > 2.8 or $y < 0.2 or $y > 1.8) {
			return 0;
		}
		else {
			return 1;
		}
	}
}

class Drapeau3 extends Drapeau {

	protected $zones = array(
		array(
			0, 0,
			1.5, 0,
			1.5, 1,
			0, 1,
		)
	);

	public function get_zone($x, $y) {
		$x = $x / $this->factor;
		$y = $y / $this->factor;
		if ($x < 1.5 and $y < 1) {
			return 1;
		}
		else {
			return 0;
		}
	}
}

class Drapeau4 extends Drapeau {

	protected $zones = array(
		array(
			0, 0,
			1.5, 1,
			0, 2,
		),
	);

	public function get_zone($x, $y) {
		if (($y + (2/3)*$x - (2 * $this->factor) > 0) or ($y - (2/3)*$x < 0)) {
			return 0;
		}
		else {
			return 1;
		}
	}
}

class Drapeau5 extends Drapeau {

	protected $zones = array(
		array(
			0, 0,
			3, 0,
			3, 0.66666666667,
			0, 0.66666666667,
		),
		array(
			0, 1.33333333333,
			3, 1.33333333333,
			3, 2,
			0, 2,
		),
	);

	public function get_zone($x, $y) {
		$y = $y / $this->factor;
		if ($y < 0.66666666667) {
			return 1;
		}
		else if ($y > 1.33333333333) {
			return 2;
		}
		else {
			return 0;
		}
	}
}

class Drapeau6 extends Drapeau {
	protected $zones = array(
		array(
			1.3, 0.4,
			1.7, 0.4,
			1.7, 0.8,
			2.1, 0.8,
			2.1, 1.2,
			1.7, 1.2,
			1.7, 1.6,
			1.3, 1.6,
			1.3, 1.2,
			0.9, 1.2,
			0.9, 0.8,
			1.3, 0.8,
		),
	);

	public function get_zone($x, $y) {
		$x = $x / $this->factor;
		$y = $y / $this->factor;
		if (($x > 1.3 and $x < 1.7 and $y > 0.4 and $y < 1.6) or ($x > 0.9 and $x < 2.1 and $y > 0.8 and $y < 1.2)) {
			return 1;
		}
		else {
			return 0;
		}
	}
}

class Drapeau7 extends Drapeau {
	protected $zones = array(
		array(
			0, 0,
			1, 0,
			1, 2,
			0, 2,
		),
		array(
			2, 0,
			3, 0,
			3, 2,
			2, 2,
		),
	);

	public function get_zone($x, $y) {
		$x = $x / $this->factor;
		if ($x < 1) {
			return 1;
		}
		else if ($x > 2) {
			return 2;
		}
		else {
			return 0;
		}
	}
}

class Drapeau8 extends Drapeau {
	protected $zones = array(
		array(
			0, 0.230940108,
			1.153589838, 1,
			0, 1.769059892,
		),
		array(
			0.3, 0,
			3, 0,
			3, 0.8,
			1.5, 0.8,
		),
		array(
			0.3, 2,
			3, 2,
			3, 1.2,
			1.5, 1.2,
		),
	);
	public function get_zone($x, $y) {
		$x = $x / $this->factor;
		$y = $y / $this->factor;
		if (($y + (2.0/3)*$x - 1.769059892 < 0) and ($y - (2.0/3)*$x - 0.230940108 > 0)) {
			return 1;
		}
		else if (($y < 0.8) and ($x - (3.0/2)*$y - 0.3 > 0)) {
			return 2;
		}
		else if (($y > 1.2) and ($x + (3.0/2)*$y - 3.3 > 0)) {
			return 3;
		}
		else {
			return 0;
		}
	}
}

class Drapeau9 extends Drapeau {
	protected $zones = array(
		array(
			1.5, 0,
			3, 0,
			3, 1, 
			1.5, 1,
		),
		array(
			0, 1,
			1.5, 1,
			1.5, 2, 
			0, 2,
		),
		array(
			1.5, 1,
			3, 1,
			3, 2, 
			1.5, 2,
		),
	);

	public function get_zone($x, $y) {
		$x = $x / $this->factor;
		$y = $y / $this->factor;
		if ($y < 1) {
			return $x < 1.5 ? 0 : 1;
		}
		else {
			return $x < 1.5 ? 2 : 3;
		}
	}
}

class Drapeau10 extends Drapeau {
	protected $zones = array(
		array(
			0, 0,
			0, 0.2,
			1.2, 1,
			0, 1.8,
			0, 2,
			0.3, 2,
			1.5, 1.2,
			2.7, 2,
			3, 2,
			3, 1.8,
			1.8, 1,
			3, 0.2,
			3, 0,
			2.7, 0,
			1.5, 0.8,
			0.3, 0,
		),
	);
	public function get_zone($x, $y) {
		$x = $x / $this->factor;
		$y = $y / $this->factor;
		if ((($x - (3.0/2)*$y - 0.3 > 0) and ($x + (3.0/2)*$y - 2.7 < 0))
			or (($x + (3.0/2)*$y - 3.3 > 0) and ($x - (3.0/2)*$y - 0.3 > 0))
			or (($x - (3.0/2)*$y + 0.3 < 0) and ($x + (3.0/2)*$y - 3.3 > 0))
			or (($x + (3.0/2)*$y - 2.7 < 0) and ($x - (3.0/2)*$y + 0.3 < 0))) {
			return 0;
		}
		else {
			return 1;
		}
	}
}

class Drapeau11 extends Drapeau {

	protected $zones = array(
		array(
			0.875, 0,
			1.125, 0,
			1.125, 0.875,
			3, 0.875,
			3, 1.125,
			1.125, 1.125,
			1.125, 2,
			0.875, 2,
			0.875, 1.125,
			0, 1.125,
			0, 0.875,
			0.875, 0.875,
		),
	);

	public function get_zone($x, $y) {
		$x = $x / $this->factor;
		$y = $y / $this->factor;
		if (($x > 0.875 and $x < 1.125) or ($y > 0.875 and $y < 1.125)) {
			return 1;
		}
		else {
			return 0;
		}
	}
}

class Drapeau12 extends Drapeau {

	protected $zones = array(
		array(
			1.375, 0,
			1.625, 0,
			1.625, 0.875,
			3, 0.875,
			3, 1.125,
			1.625, 1.125,
			1.625, 2,
			1.375, 2,
			1.375, 1.125,
			0, 1.125,
			0, 0.875,
			1.375, 0.875,
		),
	);

	public function get_zone($x, $y) {
		$x = $x / $this->factor;
		$y = $y / $this->factor;
		if (($x > 1.375 and $x < 1.625) or ($y > 0.875 and $y < 1.125)) {
			return 1;
		}
		else {
			return 0;
		}
	}
}

class Drapeau13 extends Drapeau {

	protected $zones = array(
		array(1.5, 1, 1.2, 1.2),
	);

	protected $zones_types = array(1 => "ellipse");

	public function get_zone($x, $y) {
		$x = ($x / $this->factor) - 1.5;
		$y = ($y / $this->factor) - 1;
		if ($x*$x + $y*$y <= 0.36) {
			return 1;
		}
		else {
			return 0;
		}
	}
}
