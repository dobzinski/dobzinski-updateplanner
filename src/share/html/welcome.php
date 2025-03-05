
<!doctype html>
<html lang="en" data-bs-theme="auto">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Update Planner">
    <meta name="author" content="Robson Dobzinski">
    <title>Update Planner</title>
    <script src="lib/bootstrap-extended/js/color-modes.js"></script>
    <link href="lib/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="favicon.ico" />
    <style>
      .bd-placeholder-img {
        font-size: 1.125rem;
        text-anchor: middle;
        -webkit-user-select: none;
        -moz-user-select: none;
        user-select: none;
      }
      @media (min-width: 768px) {
        .bd-placeholder-img-lg {
          font-size: 3.5rem;
        }
      }
      .b-example-divider {
        width: 100%;
        height: 3rem;
        background-color: rgba(0, 0, 0, .1);
        border: solid rgba(0, 0, 0, .15);
        border-width: 1px 0;
        box-shadow: inset 0 .5em 1.5em rgba(0, 0, 0, .1), inset 0 .125em .5em rgba(0, 0, 0, .15);
      }
      .b-example-vr {
        flex-shrink: 0;
        width: 1.5rem;
        height: 100vh;
      }
      .bi {
        vertical-align: -.125em;
        fill: currentColor;
      }
      .nav-scroller {
        position: relative;
        z-index: 2;
        height: 2.75rem;
        overflow-y: hidden;
      }
      .nav-scroller .nav {
        display: flex;
        flex-wrap: nowrap;
        padding-bottom: 1rem;
        margin-top: -1px;
        overflow-x: auto;
        text-align: center;
        white-space: nowrap;
        -webkit-overflow-scrolling: touch;
      }
      [data-bs-theme=light] {
        --theme-form-background: #F2F2F2;
      }
      [data-bs-theme=dark] {
        --theme-form-background: #2B3035;
      }
      .bg-form {
        background-color: var(--theme-form-background);
      }
      .bd-mode-toggle {
        z-index: 1500;
      }
      .bd-mode-toggle .dropdown-menu .active .bi {
        display: block !important;
      }
      .btn-bd-primary {
        --bd-btncolor-bg: #6C757D;
        --bd-btncolor-rgb: 254, 124, 63;
        --bs-btn-font-weight: 600;
        --bs-btn-color: var(--bs-white);
        --bs-btn-bg: var(--bd-btncolor-bg);
        --bs-btn-border-color: var(--bd-btncolor-bg);
        --bs-btn-hover-color: var(--bs-white);
        --bs-btn-hover-bg: #6C757D;
        --bs-btn-hover-border-color: #6C757D;
        --bs-btn-focus-shadow-rgb: var(--bd-btncolor-rgb);
        --bs-btn-active-color: var(--bs-btn-hover-color);
        --bs-btn-active-bg: #6C757D;
        --bs-btn-active-border-color: #6C757D;
      }
    </style>
  </head>
  <body>
    <svg xmlns="http://www.w3.org/2000/svg" class="d-none">
      <symbol id="bi-calendar4" viewBox="0 0 16 16">
        <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M2 2a1 1 0 0 0-1 1v1h14V3a1 1 0 0 0-1-1zm13 3H1v9a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1z"/>
      </symbol>
      <symbol id="bi-calendar4-event" viewBox="0 0 16 16">
        <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M2 2a1 1 0 0 0-1 1v1h14V3a1 1 0 0 0-1-1zm13 3H1v9a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1z"/>
        <path d="M11 7.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5z"/>
      </symbol>
      <symbol id="bi-calendar4-range" viewBox="0 0 16 16">
        <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M2 2a1 1 0 0 0-1 1v1h14V3a1 1 0 0 0-1-1zm13 3H1v9a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1z"/>
        <path d="M9 7.5a.5.5 0 0 1 .5-.5H15v2H9.5a.5.5 0 0 1-.5-.5zm-2 3v1a.5.5 0 0 1-.5.5H1v-2h5.5a.5.5 0 0 1 .5.5"/>
      </symbol>
      <symbol id="bi-calendar4-week" viewBox="0 0 16 16">
        <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M2 2a1 1 0 0 0-1 1v1h14V3a1 1 0 0 0-1-1zm13 3H1v9a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1z"/>
        <path d="M11 7.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm-3 0a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm-2 3a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm-3 0a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5z"/>
      </symbol>
    </svg>
    <svg xmlns="http://www.w3.org/2000/svg" class="d-none">
      <symbol id="check2" viewBox="0 0 16 16">
        <path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0z"/>
      </symbol>
      <symbol id="circle-half" viewBox="0 0 16 16">
        <path d="M8 15A7 7 0 1 0 8 1v14zm0 1A8 8 0 1 1 8 0a8 8 0 0 1 0 16z"/>
      </symbol>
      <symbol id="moon-stars-fill" viewBox="0 0 16 16">
        <path d="M6 .278a.768.768 0 0 1 .08.858 7.208 7.208 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277.527 0 1.04-.055 1.533-.16a.787.787 0 0 1 .81.316.733.733 0 0 1-.031.893A8.349 8.349 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.752.752 0 0 1 6 .278z"/>
        <path d="M10.794 3.148a.217.217 0 0 1 .412 0l.387 1.162c.173.518.579.924 1.097 1.097l1.162.387a.217.217 0 0 1 0 .412l-1.162.387a1.734 1.734 0 0 0-1.097 1.097l-.387 1.162a.217.217 0 0 1-.412 0l-.387-1.162A1.734 1.734 0 0 0 9.31 6.593l-1.162-.387a.217.217 0 0 1 0-.412l1.162-.387a1.734 1.734 0 0 0 1.097-1.097l.387-1.162zM13.863.099a.145.145 0 0 1 .274 0l.258.774c.115.346.386.617.732.732l.774.258a.145.145 0 0 1 0 .274l-.774.258a1.156 1.156 0 0 0-.732.732l-.258.774a.145.145 0 0 1-.274 0l-.258-.774a1.156 1.156 0 0 0-.732-.732l-.774-.258a.145.145 0 0 1 0-.274l.774-.258c.346-.115.617-.386.732-.732L13.863.1z"/>
      </symbol>
      <symbol id="sun-fill" viewBox="0 0 16 16">
        <path d="M8 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM8 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 0zm0 13a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 13zm8-5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2a.5.5 0 0 1 .5.5zM3 8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2A.5.5 0 0 1 3 8zm10.657-5.657a.5.5 0 0 1 0 .707l-1.414 1.415a.5.5 0 1 1-.707-.708l1.414-1.414a.5.5 0 0 1 .707 0zm-9.193 9.193a.5.5 0 0 1 0 .707L3.05 13.657a.5.5 0 0 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707 0zm9.193 2.121a.5.5 0 0 1-.707 0l-1.414-1.414a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .707zM4.464 4.465a.5.5 0 0 1-.707 0L2.343 3.05a.5.5 0 1 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .708z"/>
      </symbol>
    </svg>
    <div class="dropdown position-fixed bottom-0 end-0 mb-3 me-3 bd-mode-toggle">
      <button class="btn btn-bd-primary py-2 dropdown-toggle d-flex align-items-center"
              id="bd-theme"
              type="button"
              aria-expanded="false"
              data-bs-toggle="dropdown"
              aria-label="Toggle theme (auto)">
        <svg class="bi my-1 theme-icon-active" width="1em" height="1em"><use href="#circle-half"></use></svg>
        <span class="visually-hidden" id="bd-theme-text">Toggle theme</span>
      </button>
      <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="bd-theme-text">
        <li>
          <button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="light" aria-pressed="false">
            <svg class="bi me-2 opacity-50" width="1em" height="1em"><use href="#sun-fill"></use></svg>
            Light
            <svg class="bi ms-auto d-none" width="1em" height="1em"><use href="#check2"></use></svg>
          </button>
        </li>
        <li>
          <button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="dark" aria-pressed="false">
            <svg class="bi me-2 opacity-50" width="1em" height="1em"><use href="#moon-stars-fill"></use></svg>
            Dark
            <svg class="bi ms-auto d-none" width="1em" height="1em"><use href="#check2"></use></svg>
          </button>
        </li>
        <li>
          <button type="button" class="dropdown-item d-flex align-items-center active" data-bs-theme-value="auto" aria-pressed="true">
            <svg class="bi me-2 opacity-50" width="1em" height="1em"><use href="#circle-half"></use></svg>
            Auto
            <svg class="bi ms-auto d-none" width="1em" height="1em"><use href="#check2"></use></svg>
          </button>
        </li>
      </ul>
    </div>
    <div class="mt-3 col-lg-8 mx-auto p-4 py-md-5">
      <header class="d-flex align-items-center pb-3 mb-5 border-bottom">
        <a href="." class="d-flex align-items-center text-body-emphasis text-decoration-none">
          <svg id="logo" class="bi text-secondary" style="height: 32px; width: 32px; margin-right: 15px;"><use xlink:href="#bi-calendar4"/></svg>
          <h2 class="fs-4 text-secondary">Update Planner<h2>
        </a>
      </header>
      <main>
        <h1 class="text-body-emphasis">Welcome!</h1>
        <p class="fs-5 col-md-8">
          This tool will support you for plan updates to your Environments and Kubernetes clusters. 
        </p>
        <div class="mt-5 mb-5">
          <div id="area">
            <a href="#" class="btn btn-primary btn-lg px-4" onclick="return getApi('login');">Let's go!</a>
          </div>
        </div>
        <hr class="col-3 col-md-2 mb-5">
        <div class="row g-5">
          <div class="col-md-4">
            <h2 class="text-body-emphasis">Projects</h2>
            <p>Check out these repositories.</p>
            <ul class="list-unstyled ps-0">
              <li>
                <a class="icon-link mb-1" href="https://github.com/dobzinski/dobzinski-updateplanner" target="_blank">
                  <svg class="bi bulleted-list mt-2" width="16" height="16"><path d="M2 2v13.5a.5.5 0 0 0 .74.439L8 13.069l5.26 2.87A.5.5 0 0 0 14 15.5V2a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2"/></svg>
                  Update Planner
                </a>
              </li>
              <li>
                <a class="icon-link mb-1" href="https://github.com/SupportTools/rancher-upgrade-tool" target="_blank">
                  <svg class="bi bulleted-list mt-2" width="16" height="16"><path d="M2 2v13.5a.5.5 0 0 0 .74.439L8 13.069l5.26 2.87A.5.5 0 0 0 14 15.5V2a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2"/></svg>
                  Rancher Upgrade Tool
                </a>
              </li>
              <li>
                <a class="icon-link mb-1" href="https://github.com/rancherlabs/support-tools/tree/master/collection/rancher/v2.x/logs-collector" target="_blank">
                  <svg class="bi bulleted-list mt-2" width="16" height="16"><path d="M2 2v13.5a.5.5 0 0 0 .74.439L8 13.069l5.26 2.87A.5.5 0 0 0 14 15.5V2a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2"/></svg>
                  Logs Collector
                </a>
              </li>
              <li>
                <a class="icon-link mb-1" href="https://github.com/rancherlabs/support-tools/tree/master/collection/rancher/v2.x/supportability-review" target="_blank">
                  <svg class="bi bulleted-list mt-2" width="16" height="16"><path d="M2 2v13.5a.5.5 0 0 0 .74.439L8 13.069l5.26 2.87A.5.5 0 0 0 14 15.5V2a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2"/></svg>
                  Supportability Review
                </a>
              </li>
              <li>
                <a class="icon-link mb-1" href="https://github.com/mariosergiosl/memusage" target="_blank">
                  <svg class="bi bulleted-list mt-2" width="16" height="16"><path d="M2 2v13.5a.5.5 0 0 0 .74.439L8 13.069l5.26 2.87A.5.5 0 0 0 14 15.5V2a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2"/></svg>
                  Memory Usage Tool
                </a>
              </li>
              <li>
                <a class="icon-link mb-1" href="https://github.com/mariosergiosl/susemanager-client-cleanup" target="_blank">
                  <svg class="bi bulleted-list mt-2" width="16" height="16"><path d="M2 2v13.5a.5.5 0 0 0 .74.439L8 13.069l5.26 2.87A.5.5 0 0 0 14 15.5V2a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2"/></svg>
                  SUMA Client Cleanup
                </a>
              </li>
            </ul>
          </div>
          <div class="col-md-4">
            <h2 class="text-body-emphasis">Guides</h2>
            <p>Read more details.</p>
            <ul class="list-unstyled ps-0">
              <li>
                <a class="icon-link mb-1" href="https://www.suse.com/suse-rancher/support-matrix/" target="_blank">
                  <svg class="bi bulleted-list mt-2" width="16" height="16"><path d="M2 2v13.5a.5.5 0 0 0 .74.439L8 13.069l5.26 2.87A.5.5 0 0 0 14 15.5V2a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2"/></svg>
                  Matrix of Rancher Manager
                </a>
              </li>
              <li>
                <a class="icon-link mb-1" href="https://www.suse.com/support/kb/doc/?id=000020061" target="_blank">
                  <svg class="bi bulleted-list mt-2" width="16" height="16"><path d="M2 2v13.5a.5.5 0 0 0 .74.439L8 13.069l5.26 2.87A.5.5 0 0 0 14 15.5V2a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2"/></svg>
                  Rancher Upgrade Checklist
                </a>
              </li>
              <li>
                <a class="icon-link mb-1" href="https://www.rancher.com/" target="_blank">
                  <svg class="bi bulleted-list mt-2" width="16" height="16"><path d="M2 2v13.5a.5.5 0 0 0 .74.439L8 13.069l5.26 2.87A.5.5 0 0 0 14 15.5V2a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2"/></svg>
                  Rancher
                </a>
              </li>
              <li>
                <a class="icon-link mb-1" href="https://docs.rke2.io/" target="_blank">
                  <svg class="bi bulleted-list mt-2" width="16" height="16"><path d="M2 2v13.5a.5.5 0 0 0 .74.439L8 13.069l5.26 2.87A.5.5 0 0 0 14 15.5V2a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2"/></svg>
                  RKE2
                </a>
              </li>
              <li>
                <a class="icon-link mb-1" href="https://rke.docs.rancher.com/" target="_blank">
                  <svg class="bi bulleted-list mt-2" width="16" height="16"><path d="M2 2v13.5a.5.5 0 0 0 .74.439L8 13.069l5.26 2.87A.5.5 0 0 0 14 15.5V2a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2"/></svg>
                  RKE
                </a>
              </li>
              <li>
                <a class="icon-link mb-1" href="https://k3s.io/" target="_blank">
                  <svg class="bi bulleted-list mt-2" width="16" height="16"><path d="M2 2v13.5a.5.5 0 0 0 .74.439L8 13.069l5.26 2.87A.5.5 0 0 0 14 15.5V2a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2"/></svg>
                  K3s
                </a>
              </li>
            </ul>
          </div>
          <div class="col-md-4">
            <h2 class="text-body-emphasis">Customers</h2>
            <p>Useful links.</p>
            <ul class="list-unstyled ps-0">
              <li>
                <a class="icon-link mb-1" href="https://scc.suse.com/" target="_blank">
                  <svg class="bi bulleted-list mt-2" width="16" height="16"><path d="M2 2v13.5a.5.5 0 0 0 .74.439L8 13.069l5.26 2.87A.5.5 0 0 0 14 15.5V2a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2"/></svg>
                  SUSE Customer Center
                </a>
              </li>
              <li>
                <a class="icon-link mb-1" href="https://www.suse.com/support/security/" target="_blank">
                  <svg class="bi bulleted-list mt-2" width="16" height="16"><path d="M2 2v13.5a.5.5 0 0 0 .74.439L8 13.069l5.26 2.87A.5.5 0 0 0 14 15.5V2a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2"/></svg>
                  Security
                </a>
              </li>
              <li>
                <a class="icon-link mb-1" href="https://www.suse.com/all-news/" target="_blank">
                  <svg class="bi bulleted-list mt-2" width="16" height="16"><path d="M2 2v13.5a.5.5 0 0 0 .74.439L8 13.069l5.26 2.87A.5.5 0 0 0 14 15.5V2a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2"/></svg>
                  News & Updates
                </a>
              </li>
              <li>
                <a class="icon-link mb-1" href="https://www.suse.com/c/blog/" target="_blank">
                  <svg class="bi bulleted-list mt-2" width="16" height="16"><path d="M2 2v13.5a.5.5 0 0 0 .74.439L8 13.069l5.26 2.87A.5.5 0 0 0 14 15.5V2a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2"/></svg>
                  Blog
                </a>
              </li>
            </ul>
          </div>
        </div>
      </main>
      <footer class="pt-5 my-5 text-body-secondary border-top">
          Developed by <a href="https://www.linkedin.com/in/robson-dobzinski/" class="text-muted" target="_blank">Robson Dobzinski</a>
      </footer>
    </div>
    <script src="lib/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="lib/jquery/jquery-3.7.1.min.js"></script>
    <script src="share/js/script.js"></script>
    <script src="share/js/welcome.js"></script>
  </body>
</html>
