<?php
// Jednoduchý endpoint pro stažení šablon (Word/Google Docs jako .doc HTML, nebo LaTeX)

$type = $_GET['type'] ?? 'word';

if ($type === 'latex') {
    header('Content-Type: text/x-tex; charset=UTF-8');
    header('Content-Disposition: attachment; filename="article-template.tex"');
    echo <<<'LATEX'
\documentclass[11pt,a4paper]{article}
\usepackage[czech]{babel}
\usepackage[utf8]{inputenc}
\usepackage[T1]{fontenc}
\usepackage{graphicx}
\usepackage{amsmath}
\usepackage{hyperref}

\title{Název článku}
\author{Autor Jméno}
\date{\today}

\begin{document}
\maketitle

\begin{abstract}
Stručný abstrakt článku (150--250 slov).
\end{abstract}

\textbf{Klíčová slova:} klíčové1, klíčové2, klíčové3

\section{Úvod}
Text úvodu...

\section{Metodika}
Popište data, metody a postup.

\section{Výsledky}
Hlavní výsledky.

\section{Diskuse}
Interpretace, omezení, přínos.

\section{Závěr}
Shrnutí, budoucí práce.

\begin{thebibliography}{9}
\bibitem{ref1} Autor. \textit{Název zdroje}. Rok.
\end{thebibliography}

\end{document}
LATEX;
    exit;
}

// Výchozí: Word/Google Docs jako .doc (HTML)
header('Content-Type: application/msword; charset=UTF-8');
header('Content-Disposition: attachment; filename="article-template.doc"');

echo '<html><head><meta charset="UTF-8"><title>Šablona článku</title></head><body>';
echo '<h1>Název článku</h1>';
echo '<p><strong>Autoři:</strong> Jméno1, Jméno2</p>';
echo '<h2>Abstrakt</h2><p>Stručný souhrn (150–250 slov).</p>';
echo '<p><strong>Klíčová slova:</strong> klíčové1, klíčové2, klíčové3</p>';
echo '<h2>1. Úvod</h2><p>Text úvodu...</p>';
echo '<h2>2. Metodika</h2><p>Data, metody, postup.</p>';
echo '<h2>3. Výsledky</h2><p>Hlavní zjištění.</p>';
echo '<h2>4. Diskuse</h2><p>Interpretace, omezení.</p>';
echo '<h2>5. Závěr</h2><p>Hlavní přínos a budoucí práce.</p>';
echo '<h2>Literatura</h2><p>[1] Autor. Název zdroje. Rok.</p>';
echo '</body></html>';
exit;

