{
    const observer = new MutationObserver(function (mutations, me) {
      const $chart = document.querySelector(".c-chart-main");
      const $dropdowns = document.querySelector(".c-period-dropdowns");
  
      if ($chart && $dropdowns) {
        let isHidden = localStorage.getItem("chartHidden");
  
        const $buttonContainer = document.createElement("div");
        const $button = document.createElement("button");
        $button.innerHTML = '<i class="far fa-chart-bar">&nbsp;</i>';
        $buttonContainer.appendChild($button).classList.add("custom-mr-12");
  
        $dropdowns.parentElement.insertBefore($buttonContainer, $dropdowns);
  
        const toggleChart = function () {
          $chart.style.display = isHidden ? "none" : "block";
          if (isHidden) {
            localStorage.setItem("chartHidden", true);
            $button.classList.remove("light-gray");
          } else {
            localStorage.removeItem("chartHidden");
            $button.classList.add("light-gray");
          }
        };
  
        $button.onclick = function () {
          isHidden = !isHidden;
          toggleChart();
        };
        toggleChart();
  
        me.disconnect();
      }
    });
  
    observer.observe(document, {
      childList: true,
      subtree: true,
    });
  }
  