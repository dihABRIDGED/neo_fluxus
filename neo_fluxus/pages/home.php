<?php
/**
 * Página Home Moderna - Sistema Educacional
 * Dashboard integrado com banco de dados real
 */

session_start();
require_once '../includes/connection.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../login.php');
    exit();
}

$user_type = $_SESSION['user_type'] ?? 'aluno';
$user_id = $_SESSION['user_id'] ?? 0;
$username = htmlspecialchars($_SESSION['username'] ?? 'Usuário');

// DEBUG: Verificar dados da sessão
error_log("Dashboard - User ID: $user_id, User Type: $user_type, Username: $username");

// Buscar dados do dashboard baseado no tipo de usuário
$dashboard_data = [];
$proximas_aulas = [];
$atividades_recentes = [];

try {
    // DEBUG: Verificar conexão
    error_log("Tentando conectar ao banco...");
    
    if ($user_type === 'aluno') {
        error_log("Buscando dados para ALUNO ID: $user_id");
        
        // Dados do aluno - consulta simplificada e corrigida
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(DISTINCT m.disciplina_id) as total_disciplinas
	            FROM matricula m 
	            WHERE m.aluno_id = ? AND m.ativo = 1
        ");
        $stmt->execute([$user_id]);
        $disciplinas = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Contar frequências separadamente
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total_chamadas,
                SUM(CASE WHEN f.presente = 1 THEN 1 ELSE 0 END) as total_presencas,
                SUM(CASE WHEN f.presente = 0 THEN 1 ELSE 0 END) as total_faltas
            FROM frequencia f
            JOIN aula a ON f.aula_id = a.id
            
            WHERE f.aluno_id = ?
        ");
        $stmt->execute([$user_id]);
        $frequencia = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Combinar dados
        $dashboard_data = [
            'total_disciplinas' => $disciplinas['total_disciplinas'] ?? 0,
            'total_presencas' => $frequencia['total_presencas'] ?? 0,
            'total_faltas' => $frequencia['total_faltas'] ?? 0,
            'total_chamadas' => $frequencia['total_chamadas'] ?? 0
        ];
        
        error_log("Dados aluno: " . print_r($dashboard_data, true));
        
        // Próximas atividades do aluno
        $stmt = $pdo->prepare("
            SELECT 
                a.titulo,
                a.descricao,
                a.data_atividade,
                a.tipo,
                d.nome as disciplina_nome,
                u.nome as professor_nome
            FROM atividade a
            JOIN disciplina d ON a.disciplina_id = d.id
            JOIN usuario u ON a.criado_por = u.id
	            JOIN matricula m ON m.disciplina_id = d.id
	            WHERE m.aluno_id = ? AND m.ativo = 1 AND m.ativo = 1 AND a.data_atividade >= CURDATE()
            ORDER BY a.data_atividade ASC
            LIMIT 5
        ");
        $stmt->execute([$user_id]);
        $atividades_recentes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Próximas aulas do cronograma
        $stmt = $pdo->prepare("
            SELECT 
                d.nome as disciplina_nome,
                u.nome as professor_nome,
                c.dia_semana,
                c.horario
            FROM cronograma_semanal c
            JOIN disciplina d ON c.disciplina_id = d.id
            JOIN usuario u ON c.professor_id = u.id
            JOIN matricula m ON m.disciplina_id = d.id
            WHERE m.aluno_id = ?
            ORDER BY 
                FIELD(c.dia_semana, 'segunda', 'terca', 'quarta', 'quinta', 'sexta'),
                c.horario
            LIMIT 3
        ");
        $stmt->execute([$user_id]);
        $proximas_aulas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } elseif ($user_type === 'professor') {
        error_log("Buscando dados para PROFESSOR ID: $user_id");
        
	        // Dados do professor - consultas separadas
	        $stmt = $pdo->prepare("SELECT COUNT(*) as total_disciplinas FROM disciplina WHERE professor_id = ? AND ativo = 1");
	        $stmt->execute([$user_id]);
	        $disciplinas = $stmt->fetch(PDO::FETCH_ASSOC);
	        
	        $stmt = $pdo->prepare("SELECT COUNT(*) as total_aulas FROM aula WHERE professor_id = ?");
	        $stmt->execute([$user_id]);
	        $aulas = $stmt->fetch(PDO::FETCH_ASSOC);
	        
	        // Total de alunos únicos matriculados nas disciplinas do professor
	        $stmt = $pdo->prepare("
	            SELECT COUNT(DISTINCT m.aluno_id) as total_alunos
	            FROM matricula m
	            JOIN disciplina d ON m.disciplina_id = d.id
	            WHERE d.professor_id = ? AND m.ativo = 1
	        ");
	        $stmt->execute([$user_id]);
	        $alunos_professor = $stmt->fetch(PDO::FETCH_ASSOC);
	        
	        // Total de chamadas (registros de frequência)
	        $stmt = $pdo->prepare("
	            SELECT COUNT(*) as total_chamadas 
	            FROM frequencia f 
	            JOIN aula a ON f.aula_id = a.id 
	            WHERE a.professor_id = ?
	        ");
	        $stmt->execute([$user_id]);
	        $chamadas = $stmt->fetch(PDO::FETCH_ASSOC);
	        
	        // Total de faltas
	        $stmt = $pdo->prepare("
	            SELECT COUNT(*) as total_faltas 
	            FROM frequencia f 
	            JOIN aula a ON f.aula_id = a.id 
	            WHERE a.professor_id = ? AND f.presente = 0
	        ");
	        $stmt->execute([$user_id]);
	        $faltas = $stmt->fetch(PDO::FETCH_ASSOC);
        
	        $dashboard_data = [
	            'total_disciplinas' => $disciplinas['total_disciplinas'] ?? 0,
	            'total_aulas' => $aulas['total_aulas'] ?? 0,
	            'total_alunos' => $alunos_professor['total_alunos'] ?? 0, // Novo campo
	            'total_chamadas' => $chamadas['total_chamadas'] ?? 0,
	            'total_faltas' => $faltas['total_faltas'] ?? 0
	        ];
        
        error_log("Dados professor: " . print_r($dashboard_data, true));
        
	        // Atividades criadas pelo professor
	        $stmt = $pdo->prepare("
	            SELECT 
	                a.titulo,
	                a.descricao,
	                a.data_atividade,
	                a.tipo,
	                d.nome as disciplina_nome
	            FROM atividade a
	            JOIN disciplina d ON a.disciplina_id = d.id
	            WHERE a.criado_por = ? AND d.ativo = 1
	            ORDER BY a.criado_em DESC
	            LIMIT 5
	        ");
	        $stmt->execute([$user_id]);
	        $atividades_recentes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
	        // Dados para o novo Gráfico de Frequência Média por Disciplina
	        $stmt_frequencia = $pdo->prepare("
	            SELECT 
	                d.nome as disciplina_nome,
	                COUNT(f.id) as total_registros,
	                SUM(CASE WHEN f.presente = 1 THEN 1 ELSE 0 END) as total_presencas
	            FROM disciplina d
	            JOIN aula a ON d.id = a.disciplina_id
	            JOIN frequencia f ON a.id = f.aula_id
	            WHERE d.professor_id = ? AND d.ativo = 1
	            GROUP BY d.nome
	            ORDER BY d.nome
	        ");
	        $stmt_frequencia->execute([$user_id]);
	        $dados_grafico_frequencia = $stmt_frequencia->fetchAll(PDO::FETCH_ASSOC);
        
        // Calcular a porcentagem de frequência
        $dados_grafico_processados = [];
        foreach ($dados_grafico_frequencia as $dado) {
            $total_registros = $dado['total_registros'];
            $total_presencas = $dado['total_presencas'];
            $porcentagem = ($total_registros > 0) ? round(($total_presencas / $total_registros) * 100, 2) : 0;
            
            $dados_grafico_processados[] = [
                'disciplina' => $dado['disciplina_nome'],
                'porcentagem' => $porcentagem
            ];
        }
        
    } else {
        error_log("Buscando dados para COORDENADOR");
        
	        // Dados do coordenador - consultas simples
	        $stmt = $pdo->prepare("SELECT COUNT(*) as total_usuarios FROM usuario WHERE ativo = 1");
	        $stmt->execute();
	        $usuarios = $stmt->fetch(PDO::FETCH_ASSOC);
	        
	        $stmt = $pdo->prepare("SELECT COUNT(*) as total_alunos FROM usuario WHERE tipo = 'aluno' AND ativo = 1");
	        $stmt->execute();
	        $alunos = $stmt->fetch(PDO::FETCH_ASSOC);
	        
	        $stmt = $pdo->prepare("SELECT COUNT(*) as total_professores FROM usuario WHERE tipo = 'professor' AND ativo = 1");
	        $stmt->execute();
	        $professores = $stmt->fetch(PDO::FETCH_ASSOC);
	        
	        $stmt = $pdo->prepare("SELECT COUNT(*) as total_coordenadores FROM usuario WHERE tipo = 'coordenador' AND ativo = 1");
	        $stmt->execute();
	        $coordenadores = $stmt->fetch(PDO::FETCH_ASSOC);
	        
	        $stmt = $pdo->prepare("SELECT COUNT(*) as total_disciplinas FROM disciplina WHERE ativo = 1");
	        $stmt->execute();
	        $disciplinas = $stmt->fetch(PDO::FETCH_ASSOC);
	        
	        // Dados para o Gráfico de Usuários por Tipo (Coordenador)
	        $stmt_grafico_coordenador = $pdo->prepare("
	            SELECT tipo, COUNT(*) as total 
	            FROM usuario 
	            WHERE ativo = 1 
	            GROUP BY tipo
	        ");
	        $stmt_grafico_coordenador->execute();
	        $dados_grafico_coordenador = $stmt_grafico_coordenador->fetchAll(PDO::FETCH_ASSOC);
        
        $dashboard_data = [
            'total_usuarios' => $usuarios['total_usuarios'] ?? 0,
            'total_alunos' => $alunos['total_alunos'] ?? 0,
            'total_professores' => $professores['total_professores'] ?? 0,
            'total_coordenadores' => $coordenadores['total_coordenadores'] ?? 0,
            'total_disciplinas' => $disciplinas['total_disciplinas'] ?? 0
        ];
        
        error_log("Dados coordenador: " . print_r($dashboard_data, true));
        
	        // Atividades recentes do sistema
	        $stmt = $pdo->prepare("
	            SELECT 
	                a.titulo,
	                a.descricao,
	                a.data_atividade,
	                a.tipo,
	                d.nome as disciplina_nome,
	                u.nome as professor_nome
	            FROM atividade a
	            JOIN disciplina d ON a.disciplina_id = d.id
	            JOIN usuario u ON a.criado_por = u.id
	            WHERE d.ativo = 1
	            ORDER BY a.criado_em DESC
	            LIMIT 5
	        ");
	        $stmt->execute();
	        $atividades_recentes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
} catch (PDOException $e) {
    error_log("ERRO NO DASHBOARD: " . $e->getMessage());
    
    // Dados padrão em caso de erro
    if ($user_type === 'aluno') {
        $dashboard_data = ['total_disciplinas' => 0, 'total_faltas' => 0, 'total_presencas' => 0, 'total_chamadas' => 0];
	    } elseif ($user_type === 'professor') {
	        $dashboard_data = ['total_disciplinas' => 0, 'total_aulas' => 0, 'total_alunos' => 0, 'total_chamadas' => 0, 'total_faltas' => 0];
	    } else {
	        $dashboard_data = ['total_usuarios' => 0, 'total_alunos' => 0, 'total_professores' => 0, 'total_coordenadores' => 0, 'total_disciplinas' => 0];
	    }
    
    $proximas_aulas = [];
    $atividades_recentes = [];
}
// Garantir que todos os valores sejam números
$dashboard_data = array_map(function($value) {
    return is_numeric($value) ? $value : 0;
}, $dashboard_data);?><style>
        .dashboard-container {
            padding: var(--spacing-6);
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .welcome-section {
            background: linear-gradient(135deg, var(--primary-red), #dc2626);
            color: var(--white);
            padding: var(--spacing-8);
            border-radius: var(--border-radius-lg);
            margin-bottom: var(--spacing-8);
            position: relative;
            overflow: hidden;
        }
        
        .welcome-section::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            transform: translate(50%, -50%);
        }
        
        .welcome-title {
            font-size: var(--font-size-2xl);
            font-weight: 700;
            margin-bottom: var(--spacing-2);
        }
        
        .welcome-subtitle {
            font-size: var(--font-size-lg);
            opacity: 0.9;
            color: #FFFFFF;
        }
        
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: var(--spacing-6);
            margin-bottom: var(--spacing-8);
        }
        
        .metric-card {
            background: var(--white);
            border-radius: var(--border-radius-lg);
            padding: var(--spacing-6);
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-200);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .metric-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }
        
        .metric-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: var(--spacing-4);
        }
        
        .metric-icon {
            width: 48px;
            height: 48px;
            border-radius: var(--border-radius);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: var(--font-size-xl);
            color: var(--white);
            background: var(--primary-red);
        }
        
        .metric-value {
            font-size: var(--font-size-3xl);
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: var(--spacing-1);
        }
        
        .metric-label {
            color: var(--gray-600);
            font-size: var(--font-size-sm);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 500;
        }
        
        .metric-change {
            display: flex;
            align-items: center;
            gap: var(--spacing-2);
            font-size: var(--font-size-sm);
            margin-top: var(--spacing-2);
        }
        
        .metric-change.positive {
            color: var(--success);
        }
        
        .metric-change.negative {
            color: var(--danger);
        }
        
        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: var(--spacing-6);
        }
        
        .chart-section {
            background: var(--white);
            border-radius: var(--border-radius-lg);
            padding: var(--spacing-6);
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-200);
        }
        
        .chart-header {
            margin-bottom: var(--spacing-6);
        }
        
        .chart-title {
            font-size: var(--font-size-xl);
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: var(--spacing-2);
            display: flex;
            align-items: center;
            gap: var(--spacing-3);
        }
        
        .chart-icon {
            width: 32px;
            height: 32px;
            border-radius: var(--border-radius);
            background: var(--primary-red);
            color: var(--white);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .chart-subtitle {
            color: var(--gray-600);
            font-size: var(--font-size-sm);
        }
        
        .activity-list {
            background: var(--white);
            border-radius: var(--border-radius-lg);
            padding: var(--spacing-6);
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-200);
        }
        
        .activity-item {
            display: flex;
            align-items: center;
            gap: var(--spacing-4);
            padding: var(--spacing-4);
            border-radius: var(--border-radius);
            transition: background-color 0.2s ease;
        }
        
        .activity-item:hover {
            background: var(--gray-50);
        }
        
        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: var(--border-radius);
            background: var(--gray-100);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--gray-600);
        }
        
        .activity-content {
            flex: 1;
        }
        
        .activity-title {
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: var(--spacing-1);
        }
        
        .activity-meta {
            font-size: var(--font-size-sm);
            color: var(--gray-600);
        }
        
        .empty-state {
            text-align: center;
            padding: var(--spacing-8);
            color: var(--gray-500);
        }
        
        .empty-state i {
            font-size: var(--font-size-3xl);
            margin-bottom: var(--spacing-4);
            color: var(--gray-400);
        }
        
        .debug-info {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 10px;
            margin: 10px 0;
            font-family: monospace;
            font-size: 12px;
            color: #6c757d;
        }
        
        @media (max-width: 768px) {
            .dashboard-container {
                padding: var(--spacing-4);
            }
            
            .content-grid {
                grid-template-columns: 1fr;
            }
            
            .metrics-grid {
                grid-template-columns: 1fr;
            }
            
            .welcome-section {
                padding: var(--spacing-6);
            }
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <main class="main-content">
        <div class="dashboard-container">
            <!-- Debug info (remover em produção) -->
           
            
            <!-- Seção de boas-vindas -->
            <div class="welcome-section">
                <h1 class="welcome-title">
                    Bem-vindo, <?php echo $username; ?>!
                </h1>
                <p class="welcome-subtitle">
                    <?php 
                    if ($user_type === 'aluno') {
                        echo 'Acompanhe seu progresso acadêmico e atividades pendentes.';
                    } elseif ($user_type === 'professor') {
                        echo 'Gerencie suas disciplinas e acompanhe o desempenho dos alunos.';
                    } else {
                        echo 'Visão geral do sistema educacional e relatórios gerenciais.';
                    }
                    ?>
                </p>
            </div>
            
            <!-- Métricas principais -->
            <div class="metrics-grid">
                <?php if ($user_type === 'aluno'): ?>
                    <div class="metric-card">
                        <div class="metric-header">
                            <div class="metric-icon">
                                <i class="fas fa-book"></i>
                            </div>
                        </div>
                        <div class="metric-value"><?php echo $dashboard_data['total_disciplinas']; ?></div>
                        <div class="metric-label">Disciplinas Cursadas</div>
                    </div>
                    
                    <div class="metric-card">
                        <div class="metric-header">
                            <div class="metric-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>
                        <div class="metric-value"><?php echo $dashboard_data['total_presencas']; ?></div>
                        <div class="metric-label">Presenças</div>
                        <?php 
                        $total_chamadas = max(1, $dashboard_data['total_chamadas']);
                        $total_presencas = $dashboard_data['total_presencas'];
                        $percentual_presencas = round(($total_presencas / $total_chamadas) * 100, 1);
                        ?>
                        <div class="metric-change <?php echo $percentual_presencas >= 75 ? 'positive' : 'negative'; ?>">
                            <i class="fas fa-percentage"></i>
                            <?php echo $percentual_presencas; ?>% de frequência
                        </div>
                    </div>
                    
                    <div class="metric-card">
                        <div class="metric-header">
                            <div class="metric-icon">
                                <i class="fas fa-times-circle"></i>
                            </div>
                        </div>
                        <div class="metric-value"><?php echo $dashboard_data['total_faltas']; ?></div>
                        <div class="metric-label">Faltas</div>
                        <?php 
                        $total_faltas = $dashboard_data['total_faltas'];
                        $percentual_faltas = $total_chamadas > 0 ? ($total_faltas / $total_chamadas * 100) : 0;
                        ?>
                        <div class="metric-change <?php echo $percentual_faltas > 25 ? 'negative' : 'positive'; ?>">
                            <i class="fas fa-percentage"></i>
                            <?php echo number_format($percentual_faltas, 1); ?>% do total
                        </div>
                    </div>
                    
                    <div class="metric-card">
                        <div class="metric-header">
                            <div class="metric-icon">
                                <i class="fas fa-clipboard-list"></i>
                            </div>
                        </div>
                        <div class="metric-value"><?php echo $dashboard_data['total_chamadas']; ?></div>
                        <div class="metric-label">Total de Chamadas</div>
                    </div>
                    
                <?php elseif ($user_type === 'professor'): ?>
                    <div class="metric-card">
                        <div class="metric-header">
                            <div class="metric-icon">
                                <i class="fas fa-chalkboard-teacher"></i>
                            </div>
                        </div>
                        <div class="metric-value"><?php echo $dashboard_data['total_disciplinas']; ?></div>
                        <div class="metric-label">Disciplinas</div>
                    </div>
                    
                    <div class="metric-card">
                        <div class="metric-header">
                            <div class="metric-icon">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                        </div>
                        <div class="metric-value"><?php echo $dashboard_data['total_aulas']; ?></div>
                        <div class="metric-label">Aulas Ministradas</div>
                    </div>
                    
                    <div class="metric-card">
                        <div class="metric-header">
	                        <div class="metric-icon">
	                            <i class="fas fa-user-graduate"></i>
	                        </div>
	                    </div>
	                    <div class="metric-value"><?php echo $dashboard_data['total_alunos']; ?></div>
	                    <div class="metric-label">Alunos Matriculados</div>
	                </div>
	                
	                <div class="metric-card">
	                    <div class="metric-header">
	                        <div class="metric-icon">
	                            <i class="fas fa-clipboard-check"></i>
	                        </div>
	                    </div>
	                    <div class="metric-value"><?php echo $dashboard_data['total_chamadas']; ?></div>
	                    <div class="metric-label">Chamadas Realizadas</div>
	                </div>
	                
	                <div class="metric-card">
                        <div class="metric-header">
                            <div class="metric-icon">
                                <i class="fas fa-user-times"></i>
                            </div>
                        </div>
                        <div class="metric-value"><?php echo $dashboard_data['total_faltas']; ?></div>
                        <div class="metric-label">Faltas Registradas</div>
                    </div>
                    
                <?php else: ?>
	                    <div class="metric-card">
	                        <div class="metric-header">
	                            <div class="metric-icon">
	                                <i class="fas fa-user-tie"></i>
	                            </div>
	                        </div>
	                        <div class="metric-value"><?php echo $dashboard_data['total_coordenadores']; ?></div>
	                        <div class="metric-label">Coordenadores</div>
	                    </div>
	                    
	                    <div class="metric-card">
                        <div class="metric-header">
                            <div class="metric-icon">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                        </div>
                        <div class="metric-value"><?php echo $dashboard_data['total_alunos']; ?></div>
                        <div class="metric-label">Alunos</div>
                    </div>
                    
                    <div class="metric-card">
                        <div class="metric-header">
                            <div class="metric-icon">
                                <i class="fas fa-chalkboard-teacher"></i>
                            </div>
                        </div>
                        <div class="metric-value"><?php echo $dashboard_data['total_professores']; ?></div>
                        <div class="metric-label">Professores</div>
                    </div>
                    
                    <div class="metric-card">
                        <div class="metric-header">
                            <div class="metric-icon">
                                <i class="fas fa-book"></i>
                            </div>
                        </div>
                        <div class="metric-value"><?php echo $dashboard_data['total_disciplinas']; ?></div>
                        <div class="metric-label">Disciplinas</div>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Gráficos e atividades -->
            <div class="content-grid">
                <div class="chart-section">
                    <div class="chart-header">
                        <h3 class="chart-title">
                            <div class="chart-icon">
                                <i class="fas fa-chart-bar"></i>
                            </div>
                            <?php 
                            if ($user_type === 'aluno') {
                                echo 'Frequência Geral';
                            } elseif ($user_type === 'professor') {
                                echo 'Frequência Média por Disciplina';
                            } else {
                                echo 'Usuários por Tipo';
                            }
                            ?>
                        </h3>
	                        <p class="chart-subtitle">
	                            <?php 
	                            if ($user_type === 'aluno') {
	                                echo 'Sua frequência geral nas aulas.';
	                            } elseif ($user_type === 'professor') {
	                                echo 'Média de presença dos alunos nas suas disciplinas.';
	                            } else {
	                                echo 'Distribuição de usuários ativos no sistema.';
	                            }
	                            ?>
	                        </p>
                    </div>
                    <div style="height: 250px; position: relative;">
                        <canvas id="mainChart"></canvas>
                    </div>
                </div>
                
                <div class="activity-list">
                    <div class="chart-header">
                        <h3 class="chart-title">
                            <div class="chart-icon">
                                <i class="fas fa-list"></i>
                            </div>
                            <?php echo $user_type === 'aluno' ? 'Próximas Atividades' : 'Atividades Recentes'; ?>
                        </h3>
                    </div>
                    
                    <?php if (!empty($atividades_recentes)): ?>
                        <?php foreach ($atividades_recentes as $atividade): ?>
                            <div class="activity-item">
                                <div class="activity-icon">
                                    <i class="fas fa-<?php 
                                        $icon = 'tasks';
                                        switch ($atividade['tipo']) {
                                            case 'prova':
                                                $icon = 'file-alt';
                                                break;
                                            case 'trabalho':
                                                $icon = 'users';
                                                break;
                                            case 'exercicio':
                                                $icon = 'pencil-alt';
                                                break;
                                            case 'redacao':
                                                $icon = 'pen';
                                                break;
                                            case 'relatorio':
                                                $icon = 'chart-line';
                                                break;
                                            default:
                                                $icon = 'tasks';
                                        }
                                        echo $icon;
                                    ?>"></i>
                                </div>
                                <div class="activity-content">
                                    <div class="activity-title"><?php echo htmlspecialchars($atividade['titulo']); ?></div>
                                    <div class="activity-meta">
                                        <?php echo htmlspecialchars($atividade['disciplina_nome']); ?> • 
                                        <?php echo date('d/m/Y', strtotime($atividade['data_atividade'])); ?>
                                        <?php if (isset($atividade['professor_nome'])): ?>
                                            • <?php echo htmlspecialchars($atividade['professor_nome']); ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-calendar-check"></i>
                            <h3>Nenhuma atividade</h3>
                            <p>Não há atividades pendentes no momento.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
	    </main>
	    
	    <script>
	        // Configurar gráfico baseado no tipo de usuário
	        const ctx = document.getElementById('mainChart').getContext('2d');
	        
	        <?php if ($user_type === 'aluno'): ?>
	            // Gráfico de frequência para aluno
	            new Chart(ctx, {
	                type: 'doughnut',
	                data: {
	                    labels: ['Presenças', 'Faltas'],
	                    datasets: [{
	                        data: [<?php echo $dashboard_data['total_presencas']; ?>, <?php echo $dashboard_data['total_faltas']; ?>],
	                        backgroundColor: ['#10b981', '#ef4444'],
	                        borderWidth: 0,
	                        cutout: '60%'
	                    }]
	                },
	                options: {
	                    responsive: true,
	                    maintainAspectRatio: false,
	                    plugins: {
	                        legend: {
	                            position: 'bottom',
	                            labels: {
	                                padding: 20,
	                                usePointStyle: true
	                            }
	                        }
	                    }
	                }
	            });
<?php elseif ($user_type === 'professor'): ?>
		            // Gráfico de Frequência Média por Disciplina para professor
		            const dadosGrafico = <?php echo json_encode($dados_grafico_processados ?? []); ?>;
		            const labels = dadosGrafico.map(d => d.disciplina);
		            const data = dadosGrafico.map(d => d.porcentagem);
		            
		            new Chart(ctx, {
		                type: 'bar',
		                data: {
		                    labels: labels,
		                    datasets: [{
		                        label: 'Frequência Média (%)',
		                        data: data,
		                        backgroundColor: '#dc2626',
		                        borderRadius: 4
		                    }]
		                },
		                options: {
		                    responsive: true,
		                    maintainAspectRatio: false,
		                    plugins: {
		                        legend: {
		                            display: false
		                        },
		                        tooltip: {
		                            callbacks: {
		                                label: function(context) {
		                                    let label = context.dataset.label || '';
		                                    if (label) {
		                                        label += ': ';
		                                    }
		                                    if (context.parsed.y !== null) {
		                                        label += context.parsed.y + '%';
		                                    }
		                                    return label;
		                                }
		                            }
		                        }
		                    },
		                    scales: {
		                        y: {
		                            beginAtZero: true,
		                            max: 100,
		                            title: {
		                                display: true,
		                                text: 'Porcentagem de Presença'
		                            }
		                        }
		                    }
		                }
		            });
		        <?php else: ?>
		            // Gráfico de usuários para coordenador
		            const dadosGraficoCoordenador = <?php echo json_encode($dados_grafico_coordenador ?? []); ?>;
		            const labelsCoordenador = dadosGraficoCoordenador.map(d => {
		                if (d.tipo === 'aluno') return 'Alunos';
		                if (d.tipo === 'professor') return 'Professores';
		                if (d.tipo === 'coordenador') return 'Coordenadores';
		                return d.tipo;
		            });
		            const dataCoordenador = dadosGraficoCoordenador.map(d => d.total);
		            
		            new Chart(ctx, {
		                type: 'doughnut',
		                data: {
		                    labels: labelsCoordenador,
		                    datasets: [{
		                        data: dataCoordenador,
		                        backgroundColor: ['#3b82f6', '#dc2626', '#f59e0b'], // Azul, Vermelho, Amarelo
		                        borderWidth: 0,
		                        cutout: '60%'
		                    }]
		                },
		                options: {
		                    responsive: true,
		                    maintainAspectRatio: false,
		                    plugins: {
		                        legend: {
		                            position: 'bottom',
		                            labels: {
		                                padding: 20,
		                                usePointStyle: true
		                            }
		                        }
		                    }
		                }
		            });
		        <?php endif; ?>
	    </script>
