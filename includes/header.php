<?php
// Incluindo configurações do banco de dados
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nofap Grupo XXX</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
        }

        .container {
            max-width: 768px;
            margin: 0 auto;
            padding: 20px;
            display: flex;
            min-height: 100vh;
        }

        /* Header com imagem */
        .header {
            width: 100%;
            text-align: center;
            padding: 0;
            margin: 0;
            background-color: #2c3e50;
        }

        .header-image {
            width: 170px;
            height: 140px;
            object-fit: cover;
            object-position: center;
            border-radius: 20px;
            margin: 0 auto 10px;
            border: none;
            padding: 0;
            display: block;
            box-sizing: border-box;
            max-width: 100%;
            height: auto;
        }

        .header-content {
            background-color: #2c3e50;
            color: white;
            padding: 10px 0;
            margin: 0;
            border-radius: 0 0 8px 8px;
            width: 100%;
        }

        .header-content h1 {
            margin: 0 0 5px 0;
            font-size: 1.3em;
            font-family: 'Crimson Text', Georgia, serif;
        }

        .header-content p {
            margin: 0;
            font-size: 0.9em;
        }

        /* Main content area */
        .main-content {
            display: flex;
            flex: 1;
            gap: 20px;
            margin-top: 20px;
        }

        /* Column 1 - Conteúdo Principal */
        .column-main {
            flex: 3;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .posts-list {
            margin-top: 20px;
        }

        .post-item {
            border-bottom: 1px solid #eee;
            padding: 20px 0;
        }

        .post-item:last-child {
            border-bottom: none;
        }

        .post-title {
            color: #2c3e50;
            margin-bottom: 10px;
            font-size: 1.3em;
        }

        .post-date {
            color: #7f8c8d;
            font-size: 0.9em;
            margin-bottom: 10px;
        }

        .post-description {
            color: #555;
            margin-bottom: 15px;
        }

        .post-text {
            color: #666;
            line-height: 1.6;
        }

        /* Column 2 - Menu Lateral */
        .column-sidebar {
            flex: 1;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            height: fit-content;
        }

        .sidebar-title {
            color: #2c3e50;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #3498db;
        }

        .sidebar-links {
            list-style: none;
        }

        .sidebar-links li {
            margin-bottom: 10px;
        }

        .sidebar-links a {
            color: #3498db;
            text-decoration: none;
            transition: color 0.3s;
        }

        .sidebar-links a:hover {
            color: #2980b9;
            text-decoration: underline;
        }

        /* Pagination */
        .pagination {
            margin-top: 30px;
            text-align: center;
        }

        .pagination a {
            display: inline-block;
            padding: 8px 16px;
            margin: 0 5px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }

        .pagination a:hover {
            background-color: #2980b9;
        }

        .pagination .current {
            background-color: #2c3e50;
            cursor: default;
        }

        .pagination .current:hover {
            background-color: #2c3e50;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .container {
                flex-direction: column;
                padding: 10px;
            }
            
            .main-content {
                flex-direction: column;
            }
            
            .header-image {
                width: 120px;
                height: 90px;
                margin: 0 auto 8px;
            }
            
            .header-content h1 {
                font-size: 1.2em;
            }
            
            .header-content p {
                font-size: 0.8em;
            }
        }
    </style>
</head>
<body>
    <!-- Header com imagem -->
    <div class="header">
        <div style="text-align: center; padding: 10px 0;">
            <img src="uploads/images.jpg" alt="Logo do Grupo Nofap" class="header-image" onerror="this.style.display='none';">
            <div class="header-content">
                <h1>No Fap Bolteano</h1>
                <p>Bem-vindos ao desafio Nofap!</p>
            </div>
        </div>
    </div>
</body>
</html>