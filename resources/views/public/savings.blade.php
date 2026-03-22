<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Savings - Sprout</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inknut+Antiqua:wght@400;600;700&family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="sprout-font">
    <div class="sprout-appshell">
        <div class="sprout-view sprout-view--mobile">
            <div class="sprout-phone sprout-app sprout-app--mobile">
                <main class="sprout-savings-mobile">
                    @include('public.partials.savings-content', ['savingsScope' => 'mobile'])
                </main>

                @include('public.partials.nav-mobile')
            </div>
        </div>

        <div class="sprout-view sprout-view--desktop">
            <div class="sprout-savings-desktop">
                @include('public.partials.nav-desktop')

                <main class="sprout-savings-desktop__content">
                    @include('public.partials.savings-content', ['savingsScope' => 'desktop'])
                </main>
            </div>
        </div>
    </div>

    <script>
        (() => {
            const hiddenClass = 'sprout-savings__modal--hidden'
            const panels = document.querySelectorAll('[data-savings-panel]')

            panels.forEach((panel) => {
                const modal = panel.querySelector('[data-savings-transfer-modal]')
                const openButton = panel.querySelector('[data-savings-transfer-open]')
                const closeButtons = panel.querySelectorAll('[data-savings-transfer-close]')
                const donut = panel.querySelector('[data-savings-donut]')
                const popup = panel.querySelector('[data-savings-pie-popup]')
                const popupName = panel.querySelector('[data-savings-pie-popup-name]')
                const popupAmount = panel.querySelector('[data-savings-pie-popup-amount]')

                if (!modal || !openButton) {
                    return
                }

                openButton.addEventListener('click', () => {
                    modal.classList.remove(hiddenClass)
                })

                closeButtons.forEach((button) => {
                    button.addEventListener('click', () => {
                        modal.classList.add(hiddenClass)
                    })
                })

                if (donut && popup && popupName && popupAmount) {
                    const categories = JSON.parse(donut.getAttribute('data-savings-categories') || '[]')
                    const total = categories.reduce((sum, category) => sum + Number(category.amount || 0), 0)

                    const hidePopup = () => {
                        popup.classList.add('sprout-savings__pie-popup--hidden')
                    }

                    const showPopup = (category) => {
                        popupName.textContent = category.name || 'Savings'
                        popupAmount.textContent = `₱${Number(category.amount || 0).toLocaleString('en-PH')}`
                        popupAmount.style.color = category.color || '#2d9af0'
                        popup.classList.remove('sprout-savings__pie-popup--hidden')
                    }

                    donut.addEventListener('click', (event) => {
                        if (!Array.isArray(categories) || !categories.length || total <= 0) {
                            hidePopup()
                            return
                        }

                        const rect = donut.getBoundingClientRect()
                        const centerX = rect.left + (rect.width / 2)
                        const centerY = rect.top + (rect.height / 2)
                        const dx = event.clientX - centerX
                        const dy = event.clientY - centerY
                        const distance = Math.sqrt((dx * dx) + (dy * dy))
                        const outerRadius = rect.width / 2
                        const innerRadius = outerRadius - 36

                        if (distance < innerRadius || distance > outerRadius) {
                            hidePopup()
                            return
                        }

                        let angle = Math.atan2(dy, dx) * (180 / Math.PI) + 90

                        if (angle < 0) {
                            angle += 360
                        }

                        let cursor = 0
                        const selectedCategory = categories.find((category) => {
                            const portion = total > 0 ? (Number(category.amount || 0) / total) * 360 : 0
                            const start = cursor
                            const end = cursor + portion
                            cursor = end
                            return angle >= start && angle < end
                        })

                        if (!selectedCategory) {
                            hidePopup()
                            return
                        }

                        showPopup(selectedCategory)
                    })

                    document.addEventListener('click', (event) => {
                        if (!panel.contains(event.target) || (!donut.contains(event.target) && !popup.contains(event.target))) {
                            hidePopup()
                        }
                    })
                }
            })
        })()
    </script>
</body>
</html>
