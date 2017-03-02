<?php

include "lib/font_face_css_generator.php";

$fonts = new FontFaceCssGenerator("./fonts/");

echo $fonts->generate_font_face();
