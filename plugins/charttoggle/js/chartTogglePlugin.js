{

  let previousUrl = '';
  new MutationObserver(() => {
    if (location.href !== previousUrl) {
      previousUrl = location.href;
      onUrlChange();
    }
  }).observe(document, { subtree: true, childList: true });

  let observer;
  const onUrlChange = () => {

    if (observer) {
      observer.disconnect();
    }

    observer = new MutationObserver((mutations, me) => {
      const $chart = document.querySelector(".c-chart-main");
      const $dropdowns = document.querySelector(".c-period-dropdowns");
      const $toggler = document.querySelector(".c-chart-toggler");

      if ($chart && $dropdowns && !$toggler) {

        me.disconnect();

        let isHidden = localStorage.getItem("chartHidden");

        const $buttonContainer = document.createElement("div");
        const $button = document.createElement("button");
        $button.innerHTML = '<i class="far fa-chart-bar">&nbsp;</i>';
        $buttonContainer.appendChild($button).classList.add("c-chart-toggler", "custom-mr-12");

        $dropdowns.parentElement.insertBefore($buttonContainer, $dropdowns);

        const toggleChart = () => {
          $chart.style.display = isHidden ? "none" : "block";
          if (isHidden) {
            localStorage.setItem("chartHidden", true);
            $button.classList.remove("light-gray");
          } else {
            localStorage.removeItem("chartHidden");
            $button.classList.add("light-gray");
          }
        };

        $button.onclick = () => {
          isHidden = !isHidden;
          toggleChart();
        };
        toggleChart();
      }
    });

    observer.observe(document, {
      childList: true,
      subtree: true,
    });

  };


}
