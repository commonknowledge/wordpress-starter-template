
document.addEventListener('DOMContentLoaded', function () {

    // Replace default icons with theme ones and add logo to mobile nav
    function replaceOpenSVGMobileMenu() {
        let buttonWithAriaLabel = document.querySelector('button[aria-label="Open menu"]');

        if (buttonWithAriaLabel) {
            let existingSVG = buttonWithAriaLabel.querySelector('svg');
            if (existingSVG) {
                // Remove the existing SVG
                existingSVG.parentNode.removeChild(existingSVG);
            }

            // Create a new SVG element for "Open menu"
            const newSVGOpenMenu = document.createElementNS("http://www.w3.org/2000/svg", "svg");
            newSVGOpenMenu.setAttribute("width", "24");
            newSVGOpenMenu.setAttribute("height", "16");
            newSVGOpenMenu.setAttribute("viewBox", "0 0 24 16");
            newSVGOpenMenu.setAttribute("fill", "none");

            // Create the path element for the "Open menu" SVG icon
            const pathOpenMenu = document.createElementNS("http://www.w3.org/2000/svg", "path");
            pathOpenMenu.setAttribute("d", "M0.333374 15.5129V13.5129H23.6666V15.5129H0.333374ZM0.333374 9.00007V7.00014H23.6666V9.00007H0.333374ZM0.333374 2.48727V0.487305H23.6666V2.48727H0.333374Z");
            pathOpenMenu.setAttribute("fill", "#1C1B1F");

            // Append the path to the "Open menu" SVG
            newSVGOpenMenu.appendChild(pathOpenMenu);

            // Append the new "Open menu" SVG to the button
            buttonWithAriaLabel.appendChild(newSVGOpenMenu);
        }
    }

    replaceOpenSVGMobileMenu();

    // Create a div element for the container
    const containerDiv = document.createElement("div");
    containerDiv.classList.add("mobile-nav-wrapper");

    // Wrap the logo in a homepage link
    const logoLink = document.createElement("a");
    logoLink.setAttribute("href", "/");

    // Create the  SVG element for the logo
    const logoSvgElement = document.createElementNS("http://www.w3.org/2000/svg", "svg");
    logoSvgElement.setAttribute("xmlns", "http://www.w3.org/2000/svg");
    logoSvgElement.setAttribute("width", "100");
    logoSvgElement.setAttribute("height", "101");
    logoSvgElement.setAttribute("viewBox", "0 0 100 101");
    logoSvgElement.setAttribute("fill", "none");

    // Create an image element inside the logo SVG
    const logoImageElement = document.createElementNS("http://www.w3.org/2000/svg", "image");
    logoImageElement.setAttribute("width", "100");
    logoImageElement.setAttribute("height", "101");
    logoImageElement.setAttribute("href", "https://solidarityknowsnoborders.kinsta.cloud/app/uploads/2023/10/sknb-logo.png");

    // Append the image to the logo SVG
    logoSvgElement.appendChild(logoImageElement);

    // Append the logo SVG to the link
    logoLink.appendChild(logoSvgElement);


    // Create a button element for the close button
    const closeButton = document.createElement("button");
    closeButton.setAttribute("aria-label", "Close menu");
    closeButton.setAttribute("data-micromodal-close", "");
    closeButton.classList.add("wp-block-navigation__responsive-container-close");

    // Create an SVG element for the close button
    const closeButtonSVG = document.createElementNS("http://www.w3.org/2000/svg", "svg");
    closeButtonSVG.setAttribute("width", "18");
    closeButtonSVG.setAttribute("height", "18");
    closeButtonSVG.setAttribute("viewBox", "0 0 18 18");
    closeButtonSVG.setAttribute("fill", "none");

    // Create the path element for the close button SVG
    const closeButtonPath = document.createElementNS("http://www.w3.org/2000/svg", "path");
    closeButtonPath.setAttribute("d", "M1.53333 17.8717L0.128235 16.4666L7.5949 8.99994L0.128235 1.53327L1.53333 0.128174L9 7.59484L16.4667 0.128174L17.8718 1.53327L10.4051 8.99994L17.8718 16.4666L16.4667 17.8717L9 10.405L1.53333 17.8717Z");
    closeButtonPath.setAttribute("fill", "#222222");

    // Append the close button path to the close button SVG
    closeButtonSVG.appendChild(closeButtonPath);

    // Append the close button SVG to the button
    closeButton.appendChild(closeButtonSVG);

    // Append the logo link and close button to the container div
    containerDiv.appendChild(logoLink);
    containerDiv.appendChild(closeButton);

    // Get the parent of the original button element
    const buttonParent = document.querySelector(".wp-block-navigation__responsive-container-close").parentNode;

    // Replace the original button with the container div
    buttonParent.replaceChild(containerDiv, document.querySelector(".wp-block-navigation__responsive-container-close"));



    /* Generate a list of options on a drop down selector from h1 elements on the page */
    const h1Elements = document.querySelectorAll('h1');
    const linkSelector = document.getElementById('linkSelector');
    if (linkSelector) {

        h1Elements.forEach((h1, index) => {
            const option = document.createElement('option');
            option.text = h1.textContent || h1.innerText;
            option.value = index.toString(); // Set the value to the index
            linkSelector.appendChild(option);
        });

        linkSelector.addEventListener('change', function () {
            const selectedIndex = parseInt(this.value, 10);
            if (!isNaN(selectedIndex) && selectedIndex >= 0 && selectedIndex < h1Elements.length) {
                h1Elements[selectedIndex].scrollIntoView({ behavior: 'smooth' });
            }
        });
    };


    const video = document.getElementById("custom-video");
    const playButton = document.getElementById("play-button");
    const pauseButton = document.getElementById("pause-button");
    const customVideoWrapper = document.querySelector(".custom-video-wrapper");
    if (video) {
        playButton.addEventListener("click", function () {
            if (video.paused) {
                video.play();
                playButton.style.display = "none";
                pauseButton.style.display = "block";
            }
        });



        pauseButton.addEventListener("click", function () {
            if (!video.paused) {
                video.pause();
                pauseButton.style.display = "none";
                playButton.style.display = "block";
            }
        });

        // Show the pause button when the user hovers over the video
        customVideoWrapper.addEventListener("mouseenter", function () {
            if (!video.paused) {
                playButton.style.display = "none";
                pauseButton.style.display = "block";
            }
        });

        // Hide the pause button when the user moves the mouse away from the video
        customVideoWrapper.addEventListener("mouseleave", function () {
            if (!video.paused) {
                playButton.style.display = "none";
                pauseButton.style.display = "none";
            }
        });

        // Show the play button when the video ends
        video.addEventListener("ended", function () {
            playButton.style.display = "block";
            pauseButton.style.display = "none";
        });
    }

    /* Custom map block */

    /* Search form */

    const searchButton = document.getElementById('search-button');
    if (searchButton) {
        searchButton.addEventListener("click", function () {

            document.getElementById("search-form").submit();
        });
    }

    /* Display results in different places depending on screen size  */

    function moveSearchResultsBasedOnScreenSize() {
        const searchResults = document.getElementById('searchResults');
        const column1 = document.getElementsByClassName('column1')[0];
        const column2 = document.getElementsByClassName('column2')[0];
        const viewportWidth = window.innerWidth;

        if (column2) {

            if (viewportWidth < 800) {
                if (!column2.contains(searchResults)) {
                    column2.appendChild(searchResults);
                }
            } else {
                if (!column1.contains(searchResults)) {
                    column1.appendChild(searchResults);
                }
            }
        }
    }

    // Call the function on page load and whenever the window is resized
    window.addEventListener('load', moveSearchResultsBasedOnScreenSize);
    window.addEventListener('resize', moveSearchResultsBasedOnScreenSize);



    //  Make all of resource cards clickable
    const liElements = document.querySelectorAll('.type-resource');

    // Loop through each <li> element
    liElements.forEach(liElement => {
        const stretchedLink = liElement.querySelector('.stretched-link a');

        // Add a click event listener to each <li> element
        liElement.addEventListener('click', function () {
            // Check if the stretched link exists within this specific <li> element
            if (stretchedLink) {
                // Get the URL from the stretched link
                const url = stretchedLink.getAttribute('href');
                // Redirect to the URL
                window.location.href = url;
            }
        });
    });

    // Get all elements with the class "take-action"
    const clickableDivs = document.querySelectorAll(".take-action");

    // Iterate through each "take action" element and add the click event listener
    clickableDivs.forEach(function (clickableDiv) {
        // Get the anchor element within the current "take action" element
        const link = clickableDiv.querySelector(".take-action .wp-block-heading a");

        // Add a click event listener to the current "take action" element
        clickableDiv.addEventListener("click", function () {
            window.location.href = link.getAttribute("href");
        });
    });


    /* Toggle filters display on mobile  */
    const filters = document.querySelector('.filters-form');
    const filterButton = document.querySelector('.filters-toggle');

    function hideFilters() {
        filters.style.display = "none";
        filterButton.classList.remove('filters-open');
    }

    function adjustFiltersDisplay() {
        try {
            if (filters) {
                if (window.innerWidth > 767) {
                    filters.style.display = "flex";
                    filterButton.style.display = "none";
                    filterButton.classList.remove('filters-open');
                } else {
                    filterButton.style.display = "flex";
                    filterButton.classList.add('filters-open');
                    hideFilters();
                }
            }
        } catch (error) {

        }
    }

    if (filterButton) {
        filterButton.addEventListener('click', function () {
            if (filters.style.display === "flex") {
                filters.style.display = "none";
                filterButton.classList.remove('filters-open');
            } else {
                filters.style.display = "flex";
                filterButton.classList.add('filters-open');
            }
        });
    }

    adjustFiltersDisplay();
    window.addEventListener('resize', adjustFiltersDisplay);

    // Clear filters and search button

    const clearButton = document.getElementById('clearButton');
    if (clearButton) {

        function clearQueryString() {

            let currentUrl = window.location.href;

            if (currentUrl.indexOf('?') !== -1) {
                currentUrl = currentUrl.split('?')[0];
                window.location.href = currentUrl;
            }
        }

        clearButton.addEventListener('click', clearQueryString);

    }



})




