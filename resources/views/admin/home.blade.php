@extends('master.layout')

@section('content')
<div class="modern-content-wrapper">
  <!-- Dashboard Header -->
  <div class="dashboard-header">
    <h1 class="dashboard-title">Dashboard</h1>
    <div class="live-clock-card">
      <div class="clock-label">Malaysia Time</div>
      <div class="live-time" id="liveTime"></div>
      <div class="live-date" id="liveDate"></div>
    </div>
  </div>

  <!-- Real-time Stats Grid -->
  <div class="stats-grid">
    <div class="modern-stat-card primary">
      <div class="stat-header">
        <div class="stat-content">
          <div class="stat-value" id="totalStudents">{{ $totalStudents }}</div>
          <div class="stat-label">Total Students</div>
          <div class="stat-trend trend-neutral">
            <i class="fas fa-users"></i> Registered
          </div>
        </div>
        <div class="stat-icon primary">
          <i class="fas fa-users"></i>
        </div>
      </div>
    </div>

    <div class="modern-stat-card success">
      <div class="stat-header">
        <div class="stat-content">
          <div class="stat-value" id="presentToday">{{ $present }}</div>
          <div class="stat-label">Present Today</div>
          <div class="stat-trend trend-up">
            <i class="fas fa-arrow-up"></i>
            <span id="presentPercentage">{{ $totalStudents > 0 ? round(($present / $totalStudents) * 100, 1) : 0 }}%</span>
          </div>
        </div>
        <div class="stat-icon success">
          <i class="fas fa-user-check"></i>
        </div>
      </div>
    </div>

    <div class="modern-stat-card danger">
      <div class="stat-header">
        <div class="stat-content">
          <div class="stat-value" id="absentToday">{{ $absent }}</div>
          <div class="stat-label">Absent Today</div>
          <div class="stat-trend trend-down">
            <i class="fas fa-arrow-down"></i>
            <span id="absentPercentage">{{ $totalStudents > 0 ? round(($absent / $totalStudents) * 100, 1) : 0 }}%</span>
          </div>
        </div>
        <div class="stat-icon danger">
          <i class="fas fa-user-times"></i>
        </div>
      </div>
    </div>

    <div class="modern-stat-card warning">
      <div class="stat-header">
        <div class="stat-content">
          <div class="stat-value" id="lateCount">0</div>
          <div class="stat-label">Late Arrivals</div>
          <div class="stat-trend trend-warning">
            <i class="fas fa-clock"></i> After 9:00 AM
          </div>
        </div>
        <div class="stat-icon warning">
          <i class="fas fa-clock"></i>
        </div>
      </div>
    </div>

    <div class="modern-stat-card info">
      <div class="stat-header">
        <div class="stat-content">
          <div class="stat-value" id="earlyCount">0</div>
          <div class="stat-label">Early Arrivals</div>
          <div class="stat-trend trend-up">
            <i class="fas fa-arrow-up"></i> Before 8:30 AM
          </div>
        </div>
        <div class="stat-icon info">
          <i class="fas fa-user-clock"></i>
        </div>
      </div>
    </div>

    <div class="modern-stat-card secondary">
      <div class="stat-header">
        <div class="stat-content">
          <div class="stat-value" id="activeCourses">{{ $attendanceByCourse ? $attendanceByCourse->count() : 0 }}</div>
          <div class="stat-label">Active Courses</div>
          <div class="stat-trend trend-up">
            <i class="fas fa-graduation-cap"></i> Currently Running
          </div>
        </div>
        <div class="stat-icon secondary">
          <i class="fas fa-graduation-cap"></i>
        </div>
      </div>
    </div>
  </div>

  <!-- Charts Section -->
  <div class="dashboard-grid">
    <!-- Course Enrollment Bar Chart -->
    <div class="chart-card enrollment-chart-card">
      <h3 class="card-title">
        <i class="fas fa-chart-bar"></i>
        Total Enrollments by Course
        <div class="refresh-btn" onclick="refreshEnrollmentChart()">
          <i class="fas fa-sync-alt" id="enrollmentRefreshIcon"></i>
        </div>
      </h3>
      <div class="chart-container">
        <canvas id="enrollmentChart" height="300"></canvas>
      </div>
      <div class="enrollment-stats" id="enrollmentStats">
        <!-- Stats will be populated dynamically -->
      </div>
    </div>

    <!-- Course Summary with Pie Charts -->
    <div class="chart-card course-summary-card">
      <h3 class="card-title">
        <i class="fas fa-graduation-cap"></i>
        Today's Attendance by Course
        <div class="refresh-btn" onclick="refreshCourseSummary()">
          <i class="fas fa-sync-alt" id="courseSummaryRefreshIcon"></i>
        </div>
      </h3>

      <div class="course-summary-pie-grid" id="courseSummaryGrid">
        @if($attendanceByCourse && $attendanceByCourse->isNotEmpty())
          @foreach($attendanceByCourse as $index => $course)
            <div class="course-pie-item">
              <div class="course-status-indicator {{ $course->present_today > 0 ? 'active' : 'inactive' }}"></div>

              <div class="course-code-header">{{ $course->course_code }}</div>

              <div class="course-stats-text">
                {{ $course->present_today }} Present • {{ $course->total_enrolled }} Total
              </div>

              <div class="pie-chart-wrapper">
                <canvas id="coursePieChart{{ $index }}" width="100" height="100"></canvas>
                <div class="pie-percentage">
                  {{ $course->total_enrolled > 0 ? round(($course->present_today / $course->total_enrolled) * 100, 1) : 0 }}%
                </div>
              </div>
            </div>
          @endforeach
        @else
          <!-- Fallback when no data -->
          <div class="course-pie-item fallback">
            <div class="course-status-indicator inactive"></div>
            <div class="course-code-header">No Course Data</div>
            <div class="course-stats-text">Setup Required</div>
            <div class="fallback-message">
              <i class="fas fa-info-circle"></i>
              <p>Add courses and enroll students to see attendance data</p>
            </div>
          </div>
        @endif
      </div>
    </div>

    <!-- Weekly Attendance Trend -->
    <div class="chart-card">
      <h3 class="card-title">
        <i class="fas fa-chart-line"></i>
        Weekly Attendance Trend
      </h3>
      <div class="chart-container">
        <canvas id="weeklyChart" height="300"></canvas>
      </div>
    </div>

    <!-- Attendance Heatmap -->
    <div class="chart-card">
      <h3 class="card-title">
        <i class="fas fa-calendar-alt"></i>
        Attendance Heatmap
      </h3>
      <div class="heatmap-container">
        <div class="heatmap-grid" id="attendanceHeatmap">
          <!-- Heatmap will be generated dynamically -->
        </div>
        <div class="heatmap-legend">
          <span>Less</span>
          <div class="legend-colors">
            <div class="legend-color" style="background: #ebedf0;"></div>
            <div class="legend-color" style="background: #c6e48b;"></div>
            <div class="legend-color" style="background: #7bc96f;"></div>
            <div class="legend-color" style="background: #239a3b;"></div>
            <div class="legend-color" style="background: #196127;"></div>
          </div>
          <span>More</span>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
/* All your existing styles remain the same, plus these additions for pie charts */
/* Dot Legend Styles */
/* Dot Legend Styles with Labels */
.dot-legend {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 16px;
  margin-top: 10px;
}

.legend-item {
  display: flex;
  align-items: center;
  gap: 4px;
  cursor: pointer;
  transition: all 0.3s ease;
  padding: 2px 6px;
  border-radius: 4px;
}

.legend-item:hover {
  background-color: rgba(0, 0, 0, 0.05);
  transform: translateY(-1px);
}

.legend-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  flex-shrink: 0;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
}

.legend-item:hover .legend-dot {
  transform: scale(1.2);
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
}

.legend-label {
  font-size: 11px;
  font-weight: 500;
  color: #6c757d;
  white-space: nowrap;
  transition: color 0.3s ease;
}

.legend-item:hover .legend-label {
  color: #495057;
}

@media (max-width: 480px) {
  .dot-legend {
    gap: 12px;
    margin-top: 8px;
  }

  .legend-dot {
    width: 6px;
    height: 6px;
  }

  .legend-label {
    font-size: 10px;
  }

  .legend-item {
    gap: 3px;
    padding: 1px 4px;
  }
}
.modern-content-wrapper {
  background: #ffffff;
  min-height: 100vh;
  padding: 30px;
}

.dashboard-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 40px;
  background: #ffffff;
  border-bottom: 1px solid #e9ecef;
  padding: 20px 30px;
  border-radius: 20px;
  backdrop-filter: blur(20px);
  box-shadow: 0 8px 32px rgba(0,0,0,0.1);
}

.dashboard-title {
  font-size: 32px;
  font-weight: 700;
  color: #2c3e50;
}

.live-clock-card {
  text-align: right;
}

.clock-label {
  font-size: 14px;
  color: #7f8c8d;
  margin-bottom: 5px;
}

.live-time {
  font-size: 18px;
  font-weight: 600;
  color: #2ECC71;
}

.live-date {
  font-size: 14px;
  color: #7f8c8d;
  margin-top: 2px;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 25px;
  margin-bottom: 40px;
}

.modern-stat-card {
  background: rgba(255,255,255,0.95);
  padding: 30px;
  border-radius: 20px;
  backdrop-filter: blur(20px);
  box-shadow: 0 8px 32px rgba(0,0,0,0.1);
  position: relative;
  overflow: hidden;
  transition: all 0.3s ease;
}

.modern-stat-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 12px 40px rgba(0,0,0,0.15);
}

.modern-stat-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
}

.modern-stat-card.primary::before { background: #3498db; }
.modern-stat-card.success::before { background: #2ECC71; }
.modern-stat-card.danger::before { background: #e74c3c; }
.modern-stat-card.warning::before { background: #f39c12; }
.modern-stat-card.info::before { background: #17a2b8; }
.modern-stat-card.secondary::before { background: #6c757d; }

.stat-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
}

.stat-content {
  flex: 1;
}

.stat-value {
  font-size: 36px;
  font-weight: 800;
  color: #2c3e50;
  line-height: 1;
  margin-bottom: 5px;
  transition: all 0.3s ease;
}

.stat-label {
  font-size: 16px;
  color: #7f8c8d;
  margin-bottom: 10px;
}

.stat-trend {
  font-size: 14px;
  font-weight: 600;
}

.trend-up { color: #2ECC71; }
.trend-down { color: #e74c3c; }
.trend-warning { color: #f39c12; }
.trend-neutral { color: #6c757d; }

.stat-icon {
  width: 50px;
  height: 50px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  color: white;
}

.stat-icon.primary { background: linear-gradient(135deg, #3498db, #2980b9); }
.stat-icon.success { background: linear-gradient(135deg, #2ECC71, #27AE60); }
.stat-icon.danger { background: linear-gradient(135deg, #e74c3c, #c0392b); }
.stat-icon.warning { background: linear-gradient(135deg, #f39c12, #e67e22); }
.stat-icon.info { background: linear-gradient(135deg, #17a2b8, #138496); }
.stat-icon.secondary { background: linear-gradient(135deg, #6c757d, #545b62); }

.dashboard-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 30px;
  margin-bottom: 30px;
}

.chart-card {
  background: rgba(255,255,255,0.95);
  padding: 30px;
  border-radius: 20px;
  backdrop-filter: blur(20px);
  box-shadow: 0 8px 32px rgba(0,0,0,0.1);
  transition: all 0.3s ease;
  border: 1px solid rgba(0,0,0,0.05);
}

.chart-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 12px 40px rgba(0,0,0,0.15);
}

.card-title {
  font-size: 20px;
  font-weight: 700;
  color: #2c3e50;
  margin-bottom: 20px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
}

.refresh-btn {
  cursor: pointer;
  padding: 8px;
  border-radius: 8px;
  transition: all 0.3s ease;
  color: #6c757d;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  background: transparent;
  border: none;
}

.refresh-btn:hover {
  background: rgba(108, 117, 125, 0.1);
  color: #495057;
}

.chart-container {
  position: relative;
  height: 400px;
  margin-bottom: 25px;
  background: #ffffff;
  border-radius: 12px;
  padding: 20px;
  border: 1px solid rgba(0,0,0,0.08);
}

.enrollment-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
  gap: 15px;
  margin-top: 25px;
  padding-top: 25px;
  border-top: 1px solid rgba(0,0,0,0.08);
}

.enrollment-stat-item {
  text-align: center;
  padding: 16px 12px;
  background: #ffffff;
  border-radius: 12px;
  border: 1px solid rgba(0,0,0,0.06);
  transition: all 0.3s ease;
}

.enrollment-stat-item:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
  border-color: rgba(0,0,0,0.12);
}

.enrollment-stat-number {
  font-size: 24px;
  font-weight: 700;
  color: #2c3e50;
  margin-bottom: 6px;
}

.enrollment-stat-label {
  font-size: 13px;
  color: #6c757d;
  font-weight: 500;
}

/* Course Summary Pie Chart Styles */
.course-summary-pie-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 20px;
  margin-bottom: 25px;
}

.course-pie-item {
  background: #f8f9fa;
  border-radius: 12px;
  padding: 24px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  position: relative;
  transition: all 0.3s ease;
  border: 1px solid rgba(0,0,0,0.08);
  min-height: 200px;
}

.course-pie-item:hover {
  transform: translateY(-3px);
  box-shadow: 0 8px 25px rgba(0,0,0,0.12);
  border-color: rgba(52, 152, 219, 0.3);
}

.course-pie-item.fallback {
  grid-column: 1 / -1;
  background: linear-gradient(135deg, rgba(231, 76, 60, 0.05), rgba(220, 53, 69, 0.05));
  border-color: rgba(220, 53, 69, 0.2);
}

.course-status-indicator {
  position: absolute;
  top: 12px;
  right: 12px;
  width: 10px;
  height: 10px;
  border-radius: 50%;
}

.course-status-indicator.active {
  background-color: #2ECC71;
  box-shadow: 0 0 0 3px rgba(46, 204, 113, 0.2);
}

.course-status-indicator.inactive {
  background-color: #e74c3c;
  box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.2);
}

.course-code-header {
  font-size: 18px;
  font-weight: 700;
  color: #2c3e50;
  margin-bottom: 8px;
}

.course-stats-text {
  font-size: 13px;
  color: #7f8c8d;
  margin-bottom: 16px;
  font-weight: 500;
}

.pie-chart-wrapper {
  position: relative;
  margin-bottom: 12px;
}

.pie-chart-wrapper canvas {
  max-width: 100px;
  max-height: 100px;
}

.pie-percentage {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  font-size: 16px;
  font-weight: 700;
  color: #2c3e50;
  pointer-events: none;
}

.fallback-message {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 10px;
  color: #7f8c8d;
  margin-top: 20px;
}

.fallback-message i {
  font-size: 32px;
  opacity: 0.6;
}

.fallback-message p {
  margin: 0;
  font-size: 14px;
}

/* Heatmap styles */
.heatmap-container {
  padding: 20px 0;
}

.heatmap-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 3px;
  margin-bottom: 15px;
}

.heatmap-day {
  width: 35px;
  height: 35px;
  border-radius: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.heatmap-day:hover {
  transform: scale(1.1);
  box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

.heatmap-legend {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  font-size: 12px;
  color: #7f8c8d;
}

.legend-colors {
  display: flex;
  gap: 2px;
}

.legend-color {
  width: 12px;
  height: 12px;
  border-radius: 2px;
}

.spinning {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* Mobile Responsive */
@media (max-width: 768px) {
  .stats-grid {
    grid-template-columns: 1fr;
  }

  .dashboard-grid {
    grid-template-columns: 1fr;
  }

  .modern-content-wrapper {
    padding: 15px;
  }

  .course-summary-pie-grid {
    grid-template-columns: 1fr;
    gap: 15px;
  }

  .course-pie-item {
    padding: 20px;
    min-height: 180px;
  }

  .course-code-header {
    font-size: 16px;
  }

  .course-stats-text {
    font-size: 12px;
  }

  .pie-percentage {
    font-size: 14px;
  }

  .heatmap-grid {
    grid-template-columns: repeat(7, minmax(25px, 1fr));
  }

  .heatmap-day {
    width: 25px;
    height: 25px;
    font-size: 10px;
  }

  .chart-container {
    height: 300px;
    padding: 15px;
  }
}

@media (max-width: 480px) {
  .course-summary-card {
    padding: 20px;
  }

  .course-pie-item {
    min-height: 160px;
  }

  .pie-chart-wrapper canvas {
    max-width: 80px;
    max-height: 80px;
  }
}
</style>

<!-- Chart.js CDN - Load FIRST -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Global variables
let courseCharts = [];
let enrollmentChart = null;
let weeklyChart = null;
let courseSummaryCharts = [];

// Real-time clock
function updateTime() {
  const now = new Date();
  const timeOptions = {
    timeZone: "Asia/Kuala_Lumpur",
    hour: "2-digit",
    minute: "2-digit",
    second: "2-digit",
    hour12: true
  };
  const dateOptions = {
    timeZone: "Asia/Kuala_Lumpur",
    year: "numeric",
    month: "long",
    day: "numeric",
    weekday: "long"
  };

  const timeElement = document.getElementById("liveTime");
  const dateElement = document.getElementById("liveDate");

  if (timeElement) {
    timeElement.innerText = new Intl.DateTimeFormat("en-MY", timeOptions).format(now);
  }
  if (dateElement) {
    dateElement.innerText = "Today: " + new Intl.DateTimeFormat("en-MY", dateOptions).format(now);
  }
}

// Course Summary Pie Charts with Dot Legends
// Course Summary Pie Charts with Dot Legends
function initializeCourseSummaryPieCharts() {
  console.log('=== Initializing Course Summary Pie Charts with Dot Legends ===');

  // Check if Chart.js is loaded
  if (typeof Chart === 'undefined') {
    console.error('Chart.js not loaded! Waiting...');
    setTimeout(initializeCourseSummaryPieCharts, 500);
    return;
  }

  // Clear existing charts
  courseSummaryCharts.forEach(chart => {
    if (chart) chart.destroy();
  });
  courseSummaryCharts = [];

  // Get course data from Laravel
  const courseData = @json($attendanceByCourse ?? []);
  console.log('Course data:', courseData);

  if (!courseData || courseData.length === 0) {
    console.log('No course data available');
    return;
  }

  // Create pie chart for each course
  courseData.forEach((course, index) => {
    const canvasId = `coursePieChart${index}`;
    const ctx = document.getElementById(canvasId);

    if (!ctx) {
      console.error(`Canvas ${canvasId} not found`);
      return;
    }

    const enrolledCount = parseInt(course.total_enrolled) || 0;
    const presentCount = parseInt(course.present_today) || 0;
    const absentCount = Math.max(0, enrolledCount - presentCount);
    const attendancePercentage = enrolledCount > 0 ?
      Math.round((presentCount / enrolledCount) * 100 * 10) / 10 : 0;

    // Color based on attendance rate
    let primaryColor, secondaryColor;
    if (attendancePercentage >= 80) {
      primaryColor = '#2ECC71';
      secondaryColor = '#E8F5E8';
    } else if (attendancePercentage >= 60) {
      primaryColor = '#f39c12';
      secondaryColor = '#FDF2E9';
    } else if (attendancePercentage >= 40) {
      primaryColor = '#e67e22';
      secondaryColor = '#FDEDEC';
    } else {
      primaryColor = '#e74c3c';
      secondaryColor = '#FADBD8';
    }

    try {
      const chart = new Chart(ctx.getContext('2d'), {
        type: 'doughnut',
        data: {
          labels: [], // Remove labels to hide text legend
          datasets: [{
            data: [presentCount, absentCount],
            backgroundColor: [primaryColor, secondaryColor],
            borderColor: [primaryColor, primaryColor],
            borderWidth: 2,
            cutout: '70%'
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              display: false // Hide the default legend
            },
            tooltip: {
              enabled: true,
              backgroundColor: 'rgba(44, 62, 80, 0.95)',
              titleColor: '#ffffff',
              bodyColor: '#ffffff',
              borderColor: primaryColor,
              borderWidth: 2,
              cornerRadius: 8,
              callbacks: {
                title: function() {
                  return course.course_code;
                },
                label: function(context) {
                  const value = context.parsed;
                  const total = presentCount + absentCount;
                  const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                  const label = context.dataIndex === 0 ? 'Present' : 'Absent';
                  return `${label}: ${value} students (${percentage}%)`;
                }
              }
            }
          },
          animation: {
            animateRotate: true,
            duration: 1500,
            easing: 'easeOutBounce'
          },
          interaction: {
            intersect: false
          }
        }
      });

      courseSummaryCharts.push(chart);

      // Add custom dot legend after chart creation
      addDotLegendToPieChart(canvasId, primaryColor, secondaryColor, presentCount, absentCount);

      console.log(`✅ Pie chart with dot legend created for ${course.course_code}`);

    } catch (error) {
      console.error(`❌ Error creating chart for ${course.course_code}:`, error);
    }
  });

  console.log(`Created ${courseSummaryCharts.length} pie charts with dot legends`);
}

// Function to add dot legend to pie chart
function addDotLegendToPieChart(canvasId, primaryColor, secondaryColor, presentCount, absentCount) {
  const canvasElement = document.getElementById(canvasId);
  if (!canvasElement) return;

  // Find the parent course-pie-item
  const courseItem = canvasElement.closest('.course-pie-item');
  if (!courseItem) return;

  // Remove existing dot legend if any
  const existingLegend = courseItem.querySelector('.dot-legend');
  if (existingLegend) {
    existingLegend.remove();
  }

  // Create dot legend container
  const dotLegend = document.createElement('div');
  dotLegend.className = 'dot-legend';

  // Create present dot
  const presentDot = document.createElement('div');
  presentDot.className = 'legend-dot';
  presentDot.style.backgroundColor = primaryColor;
  presentDot.title = `Present: ${presentCount} students`;

  // Create absent dot
  const absentDot = document.createElement('div');
  absentDot.className = 'legend-dot';
  absentDot.style.backgroundColor = secondaryColor;
  absentDot.style.border = `1px solid ${primaryColor}`;
  absentDot.title = `Absent: ${absentCount} students`;

  dotLegend.appendChild(presentDot);
  dotLegend.appendChild(absentDot);

  // Insert the dot legend after the pie chart wrapper
  const pieWrapper = courseItem.querySelector('.pie-chart-wrapper');
  if (pieWrapper) {
    pieWrapper.insertAdjacentElement('afterend', dotLegend);
  }
}



// Function to add dot legend to pie chart
function addDotLegendToPieChart(canvasId, primaryColor, secondaryColor, presentCount, absentCount) {
  const canvasElement = document.getElementById(canvasId);
  if (!canvasElement) return;

  // Find the parent course-pie-item
  const courseItem = canvasElement.closest('.course-pie-item');
  if (!courseItem) return;

  // Remove existing dot legend if any
  const existingLegend = courseItem.querySelector('.dot-legend');
  if (existingLegend) {
    existingLegend.remove();
  }

  // Create dot legend container
  const dotLegend = document.createElement('div');
  dotLegend.className = 'dot-legend';

  // Create present dot
  const presentDot = document.createElement('div');
  presentDot.className = 'legend-dot';
  presentDot.style.backgroundColor = primaryColor;
  presentDot.title = `Present: ${presentCount} students`;

  // Create absent dot
  const absentDot = document.createElement('div');
  absentDot.className = 'legend-dot';
  absentDot.style.backgroundColor = secondaryColor;
  absentDot.style.border = `1px solid ${primaryColor}`;
  absentDot.title = `Absent: ${absentCount} students`;

  dotLegend.appendChild(presentDot);
  dotLegend.appendChild(absentDot);

  // Insert the dot legend after the pie chart wrapper
  const pieWrapper = courseItem.querySelector('.pie-chart-wrapper');
  if (pieWrapper) {
    pieWrapper.insertAdjacentElement('afterend', dotLegend);
  }
}

/* CSS Styles for Dot Legend */
/*
Add these styles to your existing CSS:

.dot-legend {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 8px;
  margin-top: 8px;
}

.legend-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.legend-dot:hover {
  transform: scale(1.3);
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
}

@media (max-width: 480px) {
  .dot-legend {
    gap: 6px;
    margin-top: 6px;
  }

  .legend-dot {
    width: 6px;
    height: 6px;
  }
}
*/

// Initialize enrollment chart
function initializeEnrollmentChart() {
  console.log('Initializing enrollment chart...');

  const chartContainer = document.querySelector('.enrollment-chart-card .chart-container');
  const statsContainer = document.getElementById('enrollmentStats');

  if (enrollmentChart) {
    enrollmentChart.destroy();
    enrollmentChart = null;
  }

  fetch('/admin/dashboard/course-enrollment', {
    headers: {
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest'
    }
  })
  .then(response => response.json())
  .then(data => {
    if (chartContainer) {
      chartContainer.innerHTML = '<canvas id="enrollmentChart" height="300"></canvas>';
    }

    const ctx = document.getElementById('enrollmentChart');
    if (!ctx || !data || data.length === 0) return;

    const sortedData = data.sort((a, b) => b.enrolled - a.enrolled);

    const gradientColors = [
      { bg: 'rgba(52, 152, 219, 0.8)', border: '#3498db' },
      { bg: 'rgba(46, 204, 113, 0.8)', border: '#2ECC71' },
      { bg: 'rgba(231, 76, 60, 0.8)', border: '#e74c3c' },
      { bg: 'rgba(243, 156, 18, 0.8)', border: '#f39c12' }
    ];

    enrollmentChart = new Chart(ctx.getContext('2d'), {
      type: 'bar',
      data: {
        labels: sortedData.map(course => course.course_code),
        datasets: [{
          label: 'Total Enrollments',
          data: sortedData.map(course => course.enrolled),
          backgroundColor: sortedData.map((_, index) => gradientColors[index % gradientColors.length].bg),
          borderColor: sortedData.map((_, index) => gradientColors[index % gradientColors.length].border),
          borderWidth: 2,
          borderRadius: 10,
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: true }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: { stepSize: 1 }
          }
        }
      }
    });

    updateEnrollmentStats(sortedData);
  })
  .catch(error => {
    console.error('Error loading enrollment data:', error);
    if (chartContainer) {
      chartContainer.innerHTML = `
        <div style="text-align: center; color: #e74c3c; padding: 40px;">
          <i class="fas fa-exclamation-triangle" style="font-size: 48px; margin-bottom: 15px;"></i>
          <p>Error loading enrollment data</p>
          <button onclick="initializeEnrollmentChart()" style="padding: 8px 16px; border: none; border-radius: 8px; background: #e74c3c; color: white; cursor: pointer; font-weight: 600; margin-top: 10px;">Retry</button>
        </div>
      `;
    }
  });
}

function updateEnrollmentStats(data) {
  const statsContainer = document.getElementById('enrollmentStats');
  if (!statsContainer || !data || data.length === 0) return;

  const mostPopularCourse = data[0];
  statsContainer.innerHTML = `
    <div class="enrollment-stat-item">
      <span class="enrollment-stat-label">
        Most Popular: <strong>${mostPopularCourse?.course_code}</strong>
        (${mostPopularCourse?.enrolled} students)
      </span>
    </div>
  `;
}

// Initialize weekly chart
function initializeWeeklyChart() {
  const ctx = document.getElementById('weeklyChart');
  if (!ctx) return;

  if (weeklyChart) {
    weeklyChart.destroy();
  }

  fetch('/admin/dashboard/weekly-attendance')
    .then(response => response.json())
    .then(data => {
      weeklyChart = new Chart(ctx.getContext('2d'), {
        type: 'line',
        data: {
          labels: data.map(d => d.day),
          datasets: [
            {
              label: 'Present',
              data: data.map(d => d.present),
              borderColor: '#2ECC71',
              backgroundColor: 'rgba(46, 204, 113, 0.1)',
              fill: true,
              tension: 0.4
            },
            {
              label: 'Absent',
              data: data.map(d => d.absent),
              borderColor: '#e74c3c',
              backgroundColor: 'rgba(231, 76, 60, 0.1)',
              fill: true,
              tension: 0.4
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { position: 'top' }
          },
          scales: {
            y: {
              beginAtZero: true,
              grid: { color: 'rgba(0,0,0,0.05)' }
            },
            x: {
              grid: { display: false }
            }
          }
        }
      });
    })
    .catch(error => console.error('Error loading weekly data:', error));
}

// Initialize attendance heatmap
function initializeHeatmap() {
  const container = document.getElementById('attendanceHeatmap');
  if (!container) return;

  const today = new Date();
  const thirtyDaysAgo = new Date(today.getTime() - 30 * 24 * 60 * 60 * 1000);

  fetch(`/admin/dashboard/attendance-stats?start_date=${thirtyDaysAgo.toISOString().split('T')[0]}&end_date=${today.toISOString().split('T')[0]}`)
    .then(response => response.json())
    .then(data => {
      container.innerHTML = '';

      for (let i = 29; i >= 0; i--) {
        const date = new Date(today.getTime() - i * 24 * 60 * 60 * 1000);
        const dateStr = date.toISOString().split('T')[0];
        const dayData = data.find(d => d.attendance_date === dateStr);

        const dayElement = document.createElement('div');
        dayElement.className = 'heatmap-day';
        dayElement.textContent = date.getDate();
        dayElement.title = `${date.toDateString()}: ${dayData ? dayData.attendance_percentage + '% attendance' : 'No data'}`;

        let color = '#ebedf0';
        if (dayData) {
          const percentage = dayData.attendance_percentage;
          if (percentage >= 90) color = '#196127';
          else if (percentage >= 75) color = '#239a3b';
          else if (percentage >= 50) color = '#7bc96f';
          else if (percentage >= 25) color = '#c6e48b';
        }

        dayElement.style.backgroundColor = color;
        dayElement.style.color = color === '#ebedf0' ? '#666' : 'white';
        container.appendChild(dayElement);
      }
    })
    .catch(error => console.error('Error loading heatmap data:', error));
}

// Refresh functions
function refreshCourseSummary() {
  const refreshIcon = document.getElementById('courseSummaryRefreshIcon');
  if (refreshIcon) {
    refreshIcon.classList.add('spinning');
  }

  const summaryGrid = document.getElementById('courseSummaryGrid');
  if (summaryGrid) {
    summaryGrid.style.opacity = '0.6';
    summaryGrid.style.pointerEvents = 'none';
  }

  setTimeout(() => {
    initializeCourseSummaryPieCharts();

    if (summaryGrid) {
      summaryGrid.style.opacity = '1';
      summaryGrid.style.pointerEvents = 'auto';
    }

    if (refreshIcon) {
      refreshIcon.classList.remove('spinning');
    }
  }, 1500);
}

function refreshEnrollmentChart() {
  const refreshIcon = document.getElementById('enrollmentRefreshIcon');
  if (refreshIcon) {
    refreshIcon.classList.add('spinning');
  }

  initializeEnrollmentChart();

  setTimeout(() => {
    if (refreshIcon) {
      refreshIcon.classList.remove('spinning');
    }
  }, 2500);
}

// Initialize everything when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
  console.log('DOM loaded, starting initialization...');

  // Start clock immediately
  updateTime();
  setInterval(updateTime, 1000);

  // Initialize charts with delays to prevent conflicts
  setTimeout(() => {
    console.log('Initializing pie charts...');
    initializeCourseSummaryPieCharts();
  }, 500);

  setTimeout(initializeEnrollmentChart, 1000);
  setTimeout(initializeWeeklyChart, 1500);
  setTimeout(initializeHeatmap, 2000);

  // Auto-refresh intervals
  setInterval(() => {
    initializeHeatmap();
  }, 60000); // Every minute
});

// Handle window resize for all charts
window.addEventListener('resize', function() {
  [enrollmentChart, weeklyChart, ...courseSummaryCharts].forEach(chart => {
    if (chart) chart.resize();
  });
});

// Debug function - call in console to check pie chart status
function debugPieCharts() {
  console.log('=== PIE CHART DEBUG ===');
  console.log('Chart.js loaded:', typeof Chart !== 'undefined');
  console.log('Course data:', @json($attendanceByCourse ?? []));
  console.log('Charts created:', courseSummaryCharts.length);

  const courseData = @json($attendanceByCourse ?? []);
  courseData.forEach((course, index) => {
    const canvas = document.getElementById(`coursePieChart${index}`);
    console.log(`Canvas coursePieChart${index}:`, canvas ? 'EXISTS' : 'MISSING');
  });
}

console.log('JavaScript loaded successfully');
</script>

@endsection










