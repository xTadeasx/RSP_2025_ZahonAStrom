<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP Data Controller Example</title>
    <style>
        body {
            background-color: #0f111a;
            color: #e2e8f0;
            font-family: 'Fira Code', monospace;
            margin: 0;
            padding: 40px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            background-color: #1a1c28;
            border-radius: 12px;
            padding: 30px 40px;
            max-width: 800px;
            box-shadow: 0 0 25px rgba(0, 0, 0, 0.5);
        }

        h1 {
            color: #58a6ff;
            font-weight: 600;
            text-align: center;
            margin-bottom: 25px;
        }

        pre {
            background-color: #0d1117;
            padding: 20px;
            border-radius: 8px;
            overflow-x: auto;
            line-height: 1.6;
            font-size: 15px;
        }

        code {
            color: #c9d1d9;
        }

        /* Basic syntax highlighting */
        .keyword {
            color: #ff7b72;
        }

        .func {
            color: #d2a8ff;
        }

        .string {
            color: #a5d6ff;
        }

        .comment {
            color: #8b949e;
            font-style: italic;
        }

        .variable {
            color: #79c0ff;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>PHP Database Control Example</h1>
        <pre><code>
<span class="keyword">require_once</span> <span class="string">'dataControll.php'</span>;

<span class="comment">// Insert</span>
<span class="func">insert</span>([<span class="string">'name'</span> => <span class="string">'John'</span>, <span class="string">'surname'</span> => <span class="string">'Doe'</span>], <span class="string">'users'</span>);

<span class="comment">// Select</span>
<span class="variable">$users</span> = <span class="func">select</span>(<span class="string">'users'</span>);

<span class="comment">// Update</span>
<span class="func">update</span>(<span class="string">'users'</span>, [<span class="string">'surname'</span> => <span class="string">'Smith'</span>], <span class="string">"name = 'John'"</span>);

<span class="comment">// Delete</span>
<span class="func">delete</span>(<span class="string">'users'</span>, <span class="string">"name = 'John'"</span>);
        </code></pre>
    </div>
</body>

</html>