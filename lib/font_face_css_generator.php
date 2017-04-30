<?php

class FontFaceCssGenerator
{

    var $fonts_directory = "./";
    var $allowed_fonts = array("ttf", "otf", "woff", "woff2", "svg", "eot");
    var $generic_fonts = array("cursive", "fantasy", "monospace", "serif", "sans-serif");
    var $font_formats = array(
        "ttf" => "truetype",
        "otf" => "opentype"
    );

    var $families = array();
    var $show_available_fonts = false;

    function __construct($path_to_fonts = "")
    {

        header("Content-Type: text/css");

        $this->fonts_directory = preg_replace('([/\\\]+)', '/', (strlen(trim($path_to_fonts)) > 0 ? $path_to_fonts : $this->fonts_directory) . '/');

        if (isset($_GET['font-family']) && strlen(trim($_GET['font-family'])) > 0) {

            $this->families = urldecode($_GET['font-family']);
            $this->families = array_map(function ($k) {
                return trim($k);
            }, explode(",", $this->families));

        }

        if (isset($_GET['list_fonts'])) {

            echo $this->list_available_fonts();

        }
        if (isset($_GET['available'])) {

            $this->show_available_fonts = true;

        }

    }

    function list_available_fonts()
    {
        if (isset($_GET['list_fonts'])) {

            $get_fonts = $this->get_all_fonts();

            if (count($get_fonts)) {

                return "# Available Fonts:" . PHP_EOL . "#--- " . implode(PHP_EOL . "#--- ", array_keys($this->get_all_fonts())) . PHP_EOL . PHP_EOL;

            }

            return "# Fonts folder is empty" . PHP_EOL;

        }

        return "";

    }

    function get_all_fonts($path = "")
    {

        $scan_path = $this->fonts_directory . $path;

        $scan_folder = is_dir($scan_path) ? scandir($scan_path) : array();
        $fonts_found = array();

        if (count($scan_folder) > 2) {

            foreach ($scan_folder as $k => $item) {

                if (!in_array($item, array(".", "..", ".git"))) {

                    if (is_dir($scan_path . '/' . $item)) {

                        $fonts_found[$item] = $this->get_all_fonts($item);

                    } else {

                        $extension = strrev(strstr(strrev(strtolower($item)), '.', true));
                        $valid_type = in_array($extension, $this->allowed_fonts);

                        if ($valid_type && @filesize($scan_path . '/' . $item) > 0) {

                            if (!isset($fonts_found['fonts'][$extension])) {
                                $fonts_found['fonts'][$extension] = array();
                            }

                            $fonts_found['fonts'][$extension][count($fonts_found['fonts'][$extension])] = array("name" => $item, "path" => $scan_path . '/' . $item);

                        }

                        if (strtolower($item) === 'default.genericfont' && @filesize($scan_path . '/' . $item) > 0) {
                            $fonts_found['genericfont'] = trim(file_get_contents($scan_path . '/' . $item));
                        }

                    }

                }

            }

            if (count($fonts_found)) {

                $fonts_found = array_filter($fonts_found, function ($k) {
                    return count($k);
                });

            }

        }

        return $fonts_found;

    }

    function retrieve_families($font_families = array())
    {

        $fonts = array();

        if (count($font_families) > 0) {

            $scan_dir = is_dir($this->fonts_directory) ? scandir($this->fonts_directory) : array();

            foreach ($font_families as $font) {

                if (($folder_name = array_search($font, $scan_dir)) != FALSE) {

                    $fonts[$scan_dir[$folder_name]] = $this->get_all_fonts($font);

                }

            }

        }

        return $fonts;

    }

    private function src_format($full_path_filename = "")
    {

        $extension = strrev(strstr(strrev(strtolower($full_path_filename)), '.', true));

        $query = $extension == 'eot' ? "?#iefix" : "";

        if (isset($this->font_formats[$extension])) {
            $extension = $this->font_formats[$extension];
        }

        return "url('" . $full_path_filename . $query . "') format('" . $extension . "')";

    }

    public function generate_font_face($found_families = array())
    {

        $found_families = count($found_families) ? $found_families : $this->families;

        $styles = array();

        $families = $this->retrieve_families($found_families);

        if (count($found_families = $families) > 0) {

            foreach ($found_families as $family_name => $font) {

                foreach ($font['fonts'] as $font_type => $find_types) {

                    foreach ($find_types as $file) {

                        $extension = strrev(strstr(strrev($file['name']), '.', true));
                        $name_no_ext = basename($file['name'], "." . $extension);

                        if (!isset($styles[$family_name]['fonts'][$name_no_ext])) {
                            $styles[$family_name]['fonts'][$name_no_ext] = array();
                        }

                        $styles[$family_name]['fonts'][$name_no_ext][] = $this->src_format($file['path']);

                    }


                }

                if (isset($font['genericfont']) && in_array($font['genericfont'], $this->generic_fonts)) {

                    $styles[$family_name]['genericfont'] = strtolower($font['genericfont']);

                }

            }

        }

        if (count($styles) > 0) {

            $html = "";

            foreach ($styles as $name => $fonts) {

                $html .= "###########################" . PHP_EOL;
                $html .= "#### $name" . PHP_EOL;

                if ($this->show_available_fonts) {
                    $html .= "#### Available Fonts:" . PHP_EOL;
                    $html .= "#------ " . implode(PHP_EOL . "#------ ", array_keys($fonts['fonts'])) . PHP_EOL;
                }

                $html .= "###########################" . PHP_EOL . PHP_EOL;

                $genericfont = isset($fonts['genericfont']) ? ", " . $fonts['genericfont'] : "";

                foreach ($fonts['fonts'] as $font_name => $src) {

                    $html .= "@font-face{" . PHP_EOL;
                    $html .= '  font-family: "' . $font_name . '"' . $genericfont . ';' . PHP_EOL;
                    $html .= '  src: ' . implode(',' . PHP_EOL . "       ", $src) . ';' . PHP_EOL;
                    $html .= "}" . PHP_EOL . PHP_EOL;

                }


            }

            $styles = $html;

        }

        return is_array($styles) ? (count($this->families) ? "# Fonts not found " : "# Specify some fonts") : $styles;

    }

}
