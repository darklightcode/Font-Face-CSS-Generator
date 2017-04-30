# Font-Face-CSS-Generator
Generate the fonts you need based on your url link.
You can use this library for your CDN to generate your css fonts on the spot.

# Usage
http://localhost:9000/?font-family=Roboto,OleoScript

# Query Options

font-family - Type your folders name that contains the fonts

list_fonts - Shows all existing fonts from your FONTS folder so you can use them with font-family parameter

available - Shows an output at the beginning of each generated font with all the font families found.

# Callback Fonts / Generic Font Types

Create a file "default.genericfont" in your font folder and write your callback font into the file:
cursive, fantasy, monospace, serif, sans-serif

You can read more about fonts on [w3.org](https://www.w3.org/Style/Examples/007/fonts.en.html) or [here](https://www.thoughtco.com/generic-font-families-in-css-3467390)