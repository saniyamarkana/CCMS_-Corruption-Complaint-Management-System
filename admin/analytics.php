<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Analytics & Intelligence Radar — CCMS Admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=Space+Grotesk:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

  <!-- Ambient Visual Glow Blobs -->
  <div class="ambient-glow-1"></div>
  <div class="ambient-glow-2"></div>
  <div class="ambient-glow-3"></div>
  <div class="grid-overlay"></div>

<div id="wrapper">
  <!-- ══════════════════════ SIDEBAR ══════════════════════ -->
  <aside id="sidebar">
    <div class="sb-brand">
      <div class="sb-logo"><i class="fa-solid fa-shield-halved"></i></div>
      <div><div class="sb-title">CCMS</div><div class="sb-sub">Anti-Corruption</div></div>
    </div>
    <nav class="sb-nav">
      <div class="sb-section-label">External Portals</div>
      <a href="../citizen/dashboard.php" class="sb-link">
        <div class="icon-wrap"><i class="fa-solid fa-users"></i></div>
        <span>Citizen Portal</span>
      </a>
      <a href="../officer/index.php" class="sb-link">
        <div class="icon-wrap"><i class="fa-solid fa-user-shield"></i></div>
        <span>Officer Command</span>
      </a>
      <a href="../index.php" class="sb-link">
        <div class="icon-wrap"><i class="fa-solid fa-globe"></i></div>
        <span>Public Sentinel</span>
      </a>

      <div class="sb-section-label">Main Overview</div>
      <a href="index.php" class="sb-link"><div class="icon-wrap"><i class="fa-solid fa-gauge-high"></i></div><span>Dashboard</span></a>
      <a href="manage-complaints.php" class="sb-link"><div class="icon-wrap"><i class="fa-solid fa-folder-open"></i></div><span>Manage Complaints</span><span class="sb-badge">12</span></a>
      <a href="assign-complaints.php" class="sb-link"><div class="icon-wrap"><i class="fa-solid fa-user-tag"></i></div><span>Assign Complaints</span><span class="sb-badge amber">5</span></a>
      
      <div class="sb-section-label">Personnel</div>
      <a href="manage-users.php" class="sb-link"><div class="icon-wrap"><i class="fa-solid fa-users"></i></div><span>Manage Users</span></a>
      <a href="manage-officers.php" class="sb-link"><div class="icon-wrap"><i class="fa-solid fa-user-shield"></i></div><span>Manage Officers</span></a>
      
      <div class="sb-section-label">System Masters</div>
      <a href="manage-departments.php" class="sb-link"><div class="icon-wrap"><i class="fa-solid fa-building-columns"></i></div><span>Departments</span></a>
      <a href="manage-categories.php" class="sb-link"><div class="icon-wrap"><i class="fa-solid fa-tags"></i></div><span>Categories</span></a>
      
      <div class="sb-section-label">Intelligence</div>
      <a href="reports.php" class="sb-link"><div class="icon-wrap"><i class="fa-solid fa-file-chart-column"></i></div><span>Reports</span></a>
      <a href="analytics.php" class="sb-link active"><div class="icon-wrap"><i class="fa-solid fa-chart-line"></i></div><span>Analytics</span></a>
      <a href="activity-logs.php" class="sb-link"><div class="icon-wrap"><i class="fa-solid fa-clock-rotate-left"></i></div><span>Activity Logs</span></a>
    </nav>
    <div class="sb-footer"><div class="sb-admin-card"><img src="https://images.unsplash.com/photo-1560250097-0b93528c311a?auto=format&fit=crop&w=80&q=80" alt="Admin" class="sb-avatar"><div class="sb-admin-info"><div class="sb-admin-name">Insp. S. Rahman</div><div class="sb-admin-role">Super Administrator</div></div></div></div>
  </aside>

  <!-- ══════════════════════ MAIN CONTENT ══════════════════════ -->
  <div id="main-content">
    <nav class="top-navbar">
      <div class="d-flex align-items-center gap-3">
        <button class="btn-toggle" id="sidebarToggle"><i class="fa-solid fa-bars-staggered"></i></button>
        <div class="search-wrap d-none d-md-block"><i class="fa-solid fa-magnifying-glass"></i><input type="text" id="tableSearch" placeholder="Search department statistics…"></div>
      </div>
      <div class="nav-actions">
        <div class="online-chip d-none d-xl-flex"><span class="online-dot"></span>Real-Time Telemetry</div>
        <button class="nav-icon-btn" id="themeToggle"><i class="fa-solid fa-moon" id="themeIcon"></i></button>
        <div class="dropdown"><div class="nav-profile-btn dropdown-toggle" data-bs-toggle="dropdown"><img src="https://images.unsplash.com/photo-1560250097-0b93528c311a?auto=format&fit=crop&w=80&q=80" class="nav-avatar" alt="Admin"><div class="d-none d-md-block"><div class="nav-profile-name">Inspector Admin</div><div class="nav-profile-role">Super Administrator</div></div></div>
          <ul class="dropdown-menu dropdown-menu-end"><li><a class="dropdown-item" href="../index.php"><i class="fa-solid fa-globe"></i> View Citizen Portal</a></li><li><a class="dropdown-item" href="activity-logs.php"><i class="fa-solid fa-clock-rotate-left"></i> My Logs</a></li><li><div class="dropdown-divider"></div></li><li><a class="dropdown-item text-danger" href="login.php"><i class="fa-solid fa-power-off"></i> Logout</a></li></ul>
        </div>
      </div>
    </nav>

    <main class="content-body">
      <div class="page-header">
        <div>
          <div class="page-breadcrumb"><a href="index.php"><i class="fa-solid fa-house"></i></a><i class="fa-solid fa-chevron-right"></i><span>Intelligence</span><i class="fa-solid fa-chevron-right"></i><span>Analytics</span></div>
          <h1 class="page-title">National Intelligence &amp; Analytics Radar</h1>
          <p class="page-subtitle">Interactive corruption heatmaps, department SLA performance, prosecution trends, and telemetry.</p>
        </div>
        <div class="d-flex gap-2">
          <button class="btn btn-primary btn-export-pdf" data-doc-name="National_Analytics_Dossier_2026"><i class="fa-solid fa-file-pdf"></i> Export Analytics Dossier</button>
        </div>
      </div>

      <!-- KPI Row -->
      <div class="kpi-grid">
        <div class="kpi-card indigo">
          <div class="kpi-icon"><i class="fa-solid fa-percent"></i></div>
          <div class="kpi-value counter-value">87.4%</div>
          <div class="kpi-label">SLA Compliance Benchmark</div>
          <div class="kpi-trend up"><i class="fa-solid fa-arrow-trend-up"></i> +3.2% vs Q2</div>
        </div>
        <div class="kpi-card emerald">
          <div class="kpi-icon"><i class="fa-solid fa-stopwatch"></i></div>
          <div class="kpi-value counter-value">4.2</div>
          <div class="kpi-label">Average Resolution (Days)</div>
          <div class="kpi-trend up"><i class="fa-solid fa-arrow-trend-up"></i> Improved from 14.2d</div>
        </div>
        <div class="kpi-card amber">
          <div class="kpi-icon"><i class="fa-solid fa-scale-balanced"></i></div>
          <div class="kpi-value counter-value">342</div>
          <div class="kpi-label">Judicial Prosecutions</div>
          <div class="kpi-trend up"><i class="fa-solid fa-arrow-trend-up"></i> +18 this month</div>
        </div>
        <div class="kpi-card rose">
          <div class="kpi-icon"><i class="fa-solid fa-money-bill-trend-up"></i></div>
          <div class="kpi-value counter-value">$28.4M</div>
          <div class="kpi-label">Public Funds Recovered</div>
          <div class="kpi-trend up"><i class="fa-solid fa-arrow-trend-up"></i> Treasury Total</div>
        </div>
      </div>

      <!-- Charts Row 1 -->
      <div class="row g-3 mb-3">
        <div class="col-lg-8">
          <div class="card-box h-100">
            <div class="card-box-head">
              <div class="card-box-title"><div class="title-icon"><i class="fa-solid fa-chart-bar"></i></div>Department-Wise Complaint Volume &amp; Clearance</div>
              <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace" style="font-size:0.75rem;">SYNC: LIVE</span>
            </div>
            <div class="card-box-body" style="padding: 16px 20px 20px;">
              <div class="chart-container-rel">
                <canvas id="deptChart"></canvas>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-4">
          <div class="card-box h-100">
            <div class="card-box-head">
              <div class="card-box-title"><div class="title-icon"><i class="fa-solid fa-chart-pie"></i></div>Resolution Outcome Distribution</div>
            </div>
            <div class="card-box-body d-flex flex-column align-items-center justify-content-center" style="padding: 16px 20px 20px;">
              <div class="chart-container-donut">
                <canvas id="outcomeChart"></canvas>
                <div class="donut-center-info">
                  <div class="donut-center-num">100%</div>
                  <div class="donut-center-txt">Disposed</div>
                </div>
              </div>
              <div class="d-flex flex-wrap gap-2 mt-3 justify-content-center">
                <div class="d-flex align-items-center gap-1 extra-small fw-bold"><span style="width:10px;height:10px;border-radius:3px;background:#10b981;display:inline-block;"></span> Resolved (54%)</div>
                <div class="d-flex align-items-center gap-1 extra-small fw-bold"><span style="width:10px;height:10px;border-radius:3px;background:#6366f1;display:inline-block;"></span> Prosecuted (23%)</div>
                <div class="d-flex align-items-center gap-1 extra-small fw-bold"><span style="width:10px;height:10px;border-radius:3px;background:#f59e0b;display:inline-block;"></span> Pending (14%)</div>
                <div class="d-flex align-items-center gap-1 extra-small fw-bold"><span style="width:10px;height:10px;border-radius:3px;background:#f43f5e;display:inline-block;"></span> Rejected (9%)</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Charts Row 2 -->
      <div class="row g-3 mb-3">
        <div class="col-lg-6">
          <div class="card-box h-100">
            <div class="card-box-head"><div class="card-box-title"><div class="title-icon"><i class="fa-solid fa-chart-line"></i></div>Monthly Trend — Filed vs Resolved (2026)</div></div>
            <div class="card-box-body" style="padding: 16px 20px 20px;">
              <div class="chart-container-rel">
                <canvas id="trendLineChart"></canvas>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="card-box h-100">
            <div class="card-box-head"><div class="card-box-title"><div class="title-icon"><i class="fa-solid fa-chart-area"></i></div>Offense Category Distribution by Severity</div></div>
            <div class="card-box-body" style="padding: 16px 20px 20px;">
              <div class="chart-container-rel">
                <canvas id="categoryChart"></canvas>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Top Corrupt Depts Table -->
      <div class="card-box">
        <div class="card-box-head">
          <div class="card-box-title"><div class="title-icon"><i class="fa-solid fa-ranking-star"></i></div>Department Integrity Vulnerability Ranking</div>
          <button class="btn btn-ghost btn-sm btn-export-pdf" data-doc-name="Department_Risk_Analysis"><i class="fa-solid fa-file-pdf" style="color:var(--rose-500);"></i> Export</button>
        </div>
        <div class="tbl-wrap">
          <table class="tbl">
            <thead><tr><th>Rank</th><th>Department</th><th>Total Cases</th><th>Critical</th><th>Resolution Rate</th><th>Avg Turnaround</th><th>Risk Tier</th></tr></thead>
            <tbody>
              <tr>
                <td><span class="fw-bold" style="color:var(--rose-500);">#1</span></td>
                <td><div class="fw-semibold" style="font-size:.82rem;">Public Works &amp; Transport</div></td>
                <td class="fw-bold text-center">284</td>
                <td class="fw-bold text-center" style="color:var(--rose-500);">68</td>
                <td><div class="progress mb-1" style="height:6px;"><div class="progress-bar" style="width:62%;background:var(--amber-500);"></div></div><div class="extra-small text-end" style="color:var(--text-2);">62%</div></td>
                <td class="fw-bold text-center">18 Days</td>
                <td><span class="chip-critical">Critical</span></td>
              </tr>
              <tr>
                <td><span class="fw-bold" style="color:var(--amber-500);">#2</span></td>
                <td><div class="fw-semibold" style="font-size:.82rem;">Land &amp; Revenue Board</div></td>
                <td class="fw-bold text-center">241</td>
                <td class="fw-bold text-center" style="color:var(--rose-500);">45</td>
                <td><div class="progress mb-1" style="height:6px;"><div class="progress-bar" style="width:71%;background:var(--cyan-500);"></div></div><div class="extra-small text-end" style="color:var(--text-2);">71%</div></td>
                <td class="fw-bold text-center">14 Days</td>
                <td><span class="chip-high">High</span></td>
              </tr>
              <tr>
                <td><span class="fw-bold" style="color:var(--indigo-500);">#3</span></td>
                <td><div class="fw-semibold" style="font-size:.82rem;">Customs &amp; Excise</div></td>
                <td class="fw-bold text-center">198</td>
                <td class="fw-bold text-center" style="color:var(--rose-500);">31</td>
                <td><div class="progress mb-1" style="height:6px;"><div class="progress-bar" style="width:78%;background:var(--emerald-500);"></div></div><div class="extra-small text-end" style="color:var(--text-2);">78%</div></td>
                <td class="fw-bold text-center">11 Days</td>
                <td><span class="chip-medium">Medium</span></td>
              </tr>
              <tr>
                <td><span class="fw-bold" style="color:var(--emerald-500);">#4</span></td>
                <td><div class="fw-semibold" style="font-size:.82rem;">Healthcare Directorate</div></td>
                <td class="fw-bold text-center">172</td>
                <td class="fw-bold text-center" style="color:var(--rose-500);">22</td>
                <td><div class="progress mb-1" style="height:6px;"><div class="progress-bar" style="width:85%;background:var(--emerald-500);"></div></div><div class="extra-small text-end" style="color:var(--text-2);">85%</div></td>
                <td class="fw-bold text-center">9 Days</td>
                <td><span class="chip-low">Low</span></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </main>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="assets/js/main.js"></script>

<script>
$(document).ready(function(){
  const gridColor = 'rgba(100,116,139,0.1)';
  const tickColor = '#94a3b8';
  const tooltipBg = 'rgba(12, 18, 34, 0.95)';
  const baseFont = { family: 'Plus Jakarta Sans', size: 12, weight: '600' };

  /* Department Bar Chart */
  const deptCanvas = document.getElementById('deptChart');
  if (deptCanvas) {
    new Chart(deptCanvas.getContext('2d'), {
      type: 'bar',
      data: {
        labels: ['Public Works','Land & Revenue','Customs','Healthcare','Education','Finance','Water Board','Police'],
        datasets: [
          { label: 'Total Filed', data: [284,241,198,172,145,130,98,88], backgroundColor: 'rgba(99,102,241,.85)', borderRadius: 8, borderSkipped: false },
          { label: 'Resolved',    data: [176,171,155,146,124,118,86,78], backgroundColor: 'rgba(16,185,129,.8)', borderRadius: 8, borderSkipped: false }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { position:'top', align:'end', labels: { boxWidth:12, boxHeight:12, borderRadius:6, useBorderRadius:true, font:baseFont, padding:16, color:'#94a3b8' } },
          tooltip: { backgroundColor:tooltipBg, titleColor:'#f8fafc', bodyColor:'#cbd5e1', borderColor:'rgba(99,102,241,.3)', borderWidth:1.5, padding:12, cornerRadius:10 }
        },
        scales: {
          x: { grid:{display:false}, ticks:{font:baseFont, color:tickColor} },
          y: { beginAtZero:true, grid:{color:gridColor}, ticks:{font:baseFont, color:tickColor} }
        }
      }
    });
  }

  /* Outcome Doughnut */
  const outcomeCanvas = document.getElementById('outcomeChart');
  if (outcomeCanvas) {
    new Chart(outcomeCanvas.getContext('2d'), {
      type: 'doughnut',
      data: {
        labels: ['Resolved','Prosecuted','Pending','Rejected'],
        datasets: [{ data: [54,23,14,9], backgroundColor: ['#10b981','#6366f1','#f59e0b','#f43f5e'], borderColor:'transparent', hoverOffset:8 }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout:'76%',
        plugins: {
          legend:{display:false},
          tooltip:{backgroundColor:tooltipBg, titleColor:'#f8fafc', bodyColor:'#cbd5e1', borderColor:'rgba(99,102,241,.3)', borderWidth:1.5, padding:10, cornerRadius:10}
        }
      }
    });
  }

  /* Trend Line Chart */
  const trendLineCanvas = document.getElementById('trendLineChart');
  if (trendLineCanvas) {
    const ctx = trendLineCanvas.getContext('2d');
    const gradRose = ctx.createLinearGradient(0, 0, 0, 260);
    gradRose.addColorStop(0, 'rgba(244,63,94,0.3)');
    gradRose.addColorStop(1, 'rgba(244,63,94,0.0)');

    const gradEmerald = ctx.createLinearGradient(0, 0, 0, 260);
    gradEmerald.addColorStop(0, 'rgba(16,185,129,0.3)');
    gradEmerald.addColorStop(1, 'rgba(16,185,129,0.0)');

    new Chart(ctx, {
      type: 'line',
      data: {
        labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug'],
        datasets: [
          { label:'Filed',    data:[120,145,190,175,210,240,275,295], borderColor:'#f43f5e', backgroundColor:gradRose, borderWidth:3, fill:true, tension:.38, pointBackgroundColor:'#f43f5e', pointBorderColor:'#fff', pointBorderWidth:2, pointRadius:4.5 },
          { label:'Resolved', data:[95,130,160,155,195,220,250,268],  borderColor:'#10b981', backgroundColor:gradEmerald, borderWidth:3, fill:true, tension:.38, pointBackgroundColor:'#10b981', pointBorderColor:'#fff', pointBorderWidth:2, pointRadius:4.5 }
        ]
      },
      options: {
        responsive:true,
        maintainAspectRatio:false,
        interaction:{mode:'index',intersect:false},
        plugins:{
          legend:{position:'top',align:'end',labels:{boxWidth:12,boxHeight:12,borderRadius:6,useBorderRadius:true,font:baseFont,padding:16,color:'#94a3b8'}},
          tooltip:{backgroundColor:tooltipBg,titleColor:'#f8fafc',bodyColor:'#cbd5e1',borderColor:'rgba(99,102,241,.3)',borderWidth:1.5,padding:12,cornerRadius:10}
        },
        scales:{
          x:{grid:{display:false},ticks:{font:baseFont,color:tickColor}},
          y:{beginAtZero:true,grid:{color:gridColor},ticks:{font:baseFont,color:tickColor}}
        }
      }
    });
  }

  /* Category Radar / Polar */
  const catCanvas = document.getElementById('categoryChart');
  if (catCanvas) {
    new Chart(catCanvas.getContext('2d'), {
      type: 'radar',
      data: {
        labels: ['Bribery','Embezzlement','Tender Fraud','Abuse of Power','Forgery','Nepotism'],
        datasets: [
          { label:'Critical', data:[68,45,55,38,22,18], borderColor:'#f43f5e', backgroundColor:'rgba(244,63,94,.18)', borderWidth:2.5, pointBackgroundColor:'#f43f5e', pointBorderColor:'#fff', pointBorderWidth:1.5 },
          { label:'High',     data:[92,80,71,62,45,38], borderColor:'#f59e0b', backgroundColor:'rgba(245,158,11,.12)', borderWidth:2.5, pointBackgroundColor:'#f59e0b', pointBorderColor:'#fff', pointBorderWidth:1.5 }
        ]
      },
      options: {
        responsive:true,
        maintainAspectRatio:false,
        plugins:{
          legend:{position:'top',align:'end',labels:{boxWidth:12,boxHeight:12,borderRadius:6,useBorderRadius:true,font:baseFont,padding:16,color:'#94a3b8'}},
          tooltip:{backgroundColor:tooltipBg,titleColor:'#f8fafc',bodyColor:'#cbd5e1',borderWidth:1.5,borderColor:'rgba(99,102,241,.3)',cornerRadius:10}
        },
        scales:{
          r:{
            grid:{color:gridColor},
            angleLines:{color:gridColor},
            pointLabels:{font:baseFont, color:tickColor},
            ticks:{font:baseFont,color:tickColor,backdropColor:'transparent'}
          }
        }
      }
    });
  }
});
</script>
</body>
</html>
