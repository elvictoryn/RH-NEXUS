<?php
if (!defined('BASE_PATH')) define('BASE_PATH','/sistema_rh');
header('Content-Type: text/plain; charset=utf-8');

require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/app/services/IAClient.php';

echo "=== PRUEBAS DE CONGRUENCIA DE IA ===\n\n";

try {
    $ia = new IAClient();

    echo "Health check:\n";
    print_r($ia->health());
    echo "\n";

    // ---------- 1) Escenarios contrastantes ----------
    $tests = [
        // Muy bajo en todo
        [
            'PersonalityScore'=>10, 'SkillScore'=>15, 'InterviewScore'=>12,
            'EducationLevel'=>'Preparatoria', 'ExperienceYears'=>0,
            'RecruitmentStrategy'=>'Portales de empleo',
            'desc'=>'Perfil MUY débil (debería dar BAJA)'
        ],
        // Promedio
        [
            'PersonalityScore'=>60, 'SkillScore'=>55, 'InterviewScore'=>58,
            'EducationLevel'=>'Licenciatura', 'ExperienceYears'=>3,
            'RecruitmentStrategy'=>'Recomendado',
            'desc'=>'Perfil MEDIO (debería dar MEDIA o BAJA)'
        ],
        // Alto
        [
            'PersonalityScore'=>85, 'SkillScore'=>80, 'InterviewScore'=>82,
            'EducationLevel'=>'Licenciatura', 'ExperienceYears'=>5,
            'RecruitmentStrategy'=>'Headhunting',
            'desc'=>'Perfil BUENO (debería dar MEDIA o ALTA)'
        ],
        // Excelente
        [
            'PersonalityScore'=>95, 'SkillScore'=>92, 'InterviewScore'=>96,
            'EducationLevel'=>'Doctorado', 'ExperienceYears'=>10,
            'RecruitmentStrategy'=>'Recomendado',
            'desc'=>'Perfil EXCELENTE (debería dar ALTA)'
        ],
    ];

    echo "=== RESULTADOS DE /predict ===\n";
    $rank_items = [];
    foreach ($tests as $i => $p) {
        $res = $ia->predict($p);
        echo "Caso ".($i+1)." — {$p['desc']}\n";
        echo sprintf("Puntajes: P=%d, S=%d, I=%d, Exp=%d, Esc=%s, Estr=%s\n",
            $p['PersonalityScore'], $p['SkillScore'], $p['InterviewScore'],
            $p['ExperienceYears'], $p['EducationLevel'], $p['RecruitmentStrategy']
        );
        print_r($res);
        echo "-----------------------------\n";
        $rank_items[] = ['id'=>$i+1, 'evaluation_score'=>$res['evaluation_score']];
    }

    // ---------- 2) Ranking ----------
    echo "\n=== RANKING SEGÚN IA ===\n";
    $rank = $ia->rank($rank_items);
    print_r($rank);

    echo "\n=== FIN DE PRUEBAS ===\n";

} catch (Throwable $e) {
    echo "ERROR: ".$e->getMessage()."\nTrace:\n".$e->getTraceAsString()."\n";
}
