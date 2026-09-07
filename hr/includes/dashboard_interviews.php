<?php

require_once __DIR__ . "/../../config/db.php";


$stmt = $conn->prepare("

    SELECT

        i.interview_date,
        i.interview_time,
        i.stage,
        i.status,

        a.first_name,
        a.last_name

    FROM interviews i

    LEFT JOIN applications a

        ON a.application_id = i.application_id

    WHERE

        i.status = 'Scheduled'

        AND i.interview_date >= CURDATE()

    ORDER BY

        i.interview_date ASC,
        i.interview_time ASC

    LIMIT 10

");

$stmt->execute();

$interviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>


<div class="hr-widget">

    <div class="hr-widget-header">

        <div class="hr-widget-title">

            <i class="fa-solid fa-calendar-check"></i>

            <h3>Upcoming Interviews</h3>

        </div>

    </div>

    <div class="hr-widget-body hr-scroll-widget">

        <?php if (empty($interviews)): ?>

            <div class="hr-empty-state">

                <i class="fa-solid fa-calendar-xmark"></i>

                <h4>No Upcoming Interviews</h4>

                <p>Scheduled interviews will appear here.</p>

            </div>

        <?php else: ?>

            <div class="hr-interview-list">

                <?php foreach ($interviews as $interview): ?>

                    <?php

                    $date = strtotime($interview["interview_date"]);

                    ?>

                    <div class="hr-interview-item">

                        <div class="hr-interview-date">

                            <strong>
                                <?= date("d", $date); ?>
                            </strong>

                            <span>
                                <?= strtoupper(date("M", $date)); ?>
                            </span>

                        </div>

                        <div class="hr-interview-details">

                            <div class="hr-interview-header">

                                <h4>

                                    <?= htmlspecialchars(

                                        trim(

                                            ($interview["first_name"] ?? "Applicant")

                                            . " "

                                            .

                                            ($interview["last_name"] ?? "")

                                        )

                                    ); ?>

                                </h4>

                                <span class="hr-interview-badge">

                                    <?= htmlspecialchars($interview["stage"]); ?>

                                </span>

                            </div>

                            <p>

                                <i class="fa-solid fa-user-check"></i>

                                <?= htmlspecialchars($interview["status"]); ?>

                            </p>

                            <p>

                                <i class="fa-solid fa-clock"></i>

                                <?= date(

                                    "h:i A",

                                    strtotime($interview["interview_time"])

                                ); ?>

                            </p>

                            <p>

                                <i class="fa-solid fa-calendar-days"></i>

                                <?= date(

                                    "F d, Y",

                                    strtotime($interview["interview_date"])

                                ); ?>

                            </p>

                        </div>

                    </div>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </div>

</div>