<?php

/**
 * Command Palette Plugin for Adminer
 * Adds a command palette with Cmd+K shortcut to quickly navigate to tables and databases
 * @link https://www.adminer.org/en/plugins/#use
 * @author Justas Raudonius
 * @license https://opensource.org/licenses/MIT MIT License
 */
class AdminerCommandPalette extends Adminer\Plugin {

    function head() {
        // Load on all pages
        if (true) {
            echo '<script nonce="' . Adminer\get_nonce() . '">
                document.addEventListener("DOMContentLoaded", function() {
                    // Create command palette HTML
                    const overlay = document.createElement("div");
                    overlay.id = "command-palette-overlay";
                    overlay.style.cssText = `
                        position: fixed;
                        top: 0;
                        left: 0;
                        width: 100%;
                        height: 100%;
                        background: rgba(0, 0, 0, 0.5);
                        backdrop-filter: blur(4px);
                        z-index: 10000;
                        display: none;
                        align-items: flex-start;
                        justify-content: center;
                        padding-top: 10vh;
                    `;

                    const palette = document.createElement("div");
                    palette.id = "command-palette";
                    palette.style.cssText = `
                        background: white;
                        border-radius: 12px;
                        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
                        width: 90%;
                        max-width: 600px;
                        max-height: 70vh;
                        overflow: hidden;
                        border: 1px solid #e5e7eb;
                        display: flex;
                        flex-direction: column;
                    `;

                    const input = document.createElement("input");
                    input.id = "command-palette-input";
                    input.type = "text";
                    input.placeholder = "Search databases and tables...";
                    input.style.cssText = `
                        width: 100%;
                        padding: 12px 12px;
                        border: none;
                        outline: none;
                        font-size: 16px;
                        border-bottom: 1px solid #e5e7eb;
                        box-sizing: border-box;
                        flex-shrink: 0;
                    `;

                    const results = document.createElement("div");
                    results.id = "command-palette-results";
                    results.style.cssText = `
                        flex: 1;
                        overflow-y: auto;
                        padding: 4px;
                        min-height: 0;
                    `;

                    palette.appendChild(input);
                    palette.appendChild(results);
                    overlay.appendChild(palette);
                    document.body.appendChild(overlay);

                    // Build data array
                    const items = [';

            // Add tables first if we are in a database
            if (Adminer\DB != "") {
                $tables = array_keys(Adminer\tables_list());
                foreach ($tables as $table) {
                    $table_escaped = Adminer\h($table);
                    $url = Adminer\ME . "select=" . urlencode($table);
                    echo '
                        {
                            name: "' . $table_escaped . '",
                            url: "' . addslashes($url) . '",
                            type: "table"
                        },';
                }
            }

            // Add databases after tables
            $databases = Adminer\get_databases(false);
            $hidden_databases = array('information_schema', 'mysql', 'performance_schema', 'sys');
            if ($databases) {
                foreach ($databases as $database) {
                    // Skip system databases
                    if (in_array($database, $hidden_databases)) {
                        continue;
                    }
                    $database_escaped = Adminer\h($database);
                    $url = Adminer\ME . "db=" . urlencode($database);
                    echo '
                        {
                            name: "' . $database_escaped . '",
                            url: "' . addslashes($url) . '",
                            type: "database"
                        },';
                }
            }

            echo '
                    ];

                    let selectedIndex = 0;
                    let filteredItems = items;
                    let isKeyboardNavigation = false;
                    let lastMouseX = 0;
                    let lastMouseY = 0;
                    let keyboardActionTime = 0;
                    let lastHoveredIndex = -1;

                                        function renderResults() {
                        results.innerHTML = "";

                        if (filteredItems.length === 0) {
                            const noResults = document.createElement("div");
                            noResults.textContent = "No results found";
                            noResults.style.cssText = `
                                padding: 12px 12px;
                                color: #6b7280;
                                text-align: center;
                            `;
                            results.appendChild(noResults);
                            return;
                        }

                        filteredItems.forEach((item, index) => {
                            const itemEl = document.createElement("div");
                            itemEl.className = "command-palette-item";
                            itemEl.style.cssText = `
                                padding: 8px 10px;
                                cursor: pointer;
                                border-radius: 6px;
                                margin: 1px 0;
                                display: flex;
                                align-items: center;
                                transition: background-color 0.1s;
                                ${index === selectedIndex && isKeyboardNavigation ? "background: #3b82f6; color: white;" : ""}
                            `;

                                                        // Add hover styles
                                                                                    itemEl.addEventListener("mouseenter", () => {
                                // Always update lastHoveredIndex when hovering
                                lastHoveredIndex = index;
                                console.log("Hovered on index:", index, "item:", filteredItems[index]?.name);

                                if (!isKeyboardNavigation) {
                                    itemEl.style.background = "#3b82f6";
                                    itemEl.style.color = "white";
                                }
                            });

                            itemEl.addEventListener("mouseleave", () => {
                                if (!isKeyboardNavigation) {
                                    itemEl.style.background = "";
                                    itemEl.style.color = "";
                                }
                            });

                            const icon = document.createElement("span");
                            if (item.type === "database") {
                                icon.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: inline-block; vertical-align: middle;"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 5V19A9 3 0 0 0 21 19V5"/><path d="M3 12A9 3 0 0 0 21 12"/></svg>`;
                            } else {
                                icon.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: inline-block; vertical-align: middle;"><path d="M9 3H5a2 2 0 0 0-2 2v4m6-6h10a2 2 0 0 1 2 2v4M9 3v18m0 0h10a2 2 0 0 0 2-2V9M9 21H5a2 2 0 0 1-2-2V9m0 0h18"/></svg>`;
                            }
                            icon.style.marginRight = "8px";

                            const name = document.createElement("span");
                            name.textContent = item.name;
                            name.style.fontWeight = "500";

                            itemEl.appendChild(icon);
                            itemEl.appendChild(name);

                            itemEl.addEventListener("click", () => {
                                window.location.href = item.url;
                            });

                            // Remove the old mouseenter that was changing selectedIndex

                            results.appendChild(itemEl);
                        });

                        // Scroll selected item into view
                        if (filteredItems.length > 0) {
                            const selectedItem = results.children[selectedIndex];
                            if (selectedItem) {
                                selectedItem.scrollIntoView({
                                    behavior: "instant",
                                    block: "nearest"
                                });
                            }
                        }
                    }

                                                                                // Fuzzy search function
                    function fuzzyMatch(text, query) {
                        if (!query) return { score: 1, matches: [] };

                        const textLower = text.toLowerCase();
                        const queryLower = query.toLowerCase();

                        // Exact match gets highest score
                        if (textLower === queryLower) return { score: 10000, matches: [] };

                        // Starts with query gets very high score
                        if (textLower.startsWith(queryLower)) return { score: 9000, matches: [] };

                        // Contains query gets high score (higher than any fuzzy match)
                        if (textLower.includes(queryLower)) return { score: 8000, matches: [] };

                        // Starts with first character and contains query gets priority
                        if (queryLower.length > 0 && textLower.startsWith(queryLower[0]) && textLower.includes(queryLower)) {
                            return { score: 7500, matches: [] };
                        }

                                                // Fuzzy matching
                        let score = 0;
                        let matches = [];
                        let queryIndex = 0;
                        let lastMatchIndex = -1;

                        for (let i = 0; i < textLower.length && queryIndex < queryLower.length; i++) {
                            if (textLower[i] === queryLower[queryIndex]) {
                                matches.push(i);

                                // Heavy bonus for first character match at position 0
                                if (queryIndex === 0 && i === 0) {
                                    score += 1200; // Much higher bonus for starting with first letter
                                }
                                // Bonus for first character match at later positions
                                else if (queryIndex === 0) {
                                    score += Math.max(100 - (i * 10), 10);
                                }
                                // Bonus for second character match at position 1 (after first char match)
                                else if (queryIndex === 1 && i === 1 && matches[0] === 0) {
                                    score += 200; // High bonus for second char in correct position
                                }
                                // Bonus for early position matches (decreasing bonus)
                                else if (queryIndex < 3) {
                                    score += Math.max(50 - (i * 5), 5);
                                }

                                // Bonus for consecutive matches
                                if (i === lastMatchIndex + 1) {
                                    score += 20;
                                }

                                // Bonus for matches at word boundaries
                                if (i === 0 || text[i - 1] === "_" || text[i - 1] === " ") {
                                    score += 15;
                                }

                                score += 5;
                                lastMatchIndex = i;
                                queryIndex++;
                            }
                        }

                        // Must match all characters
                        if (queryIndex !== queryLower.length) {
                            return { score: 0, matches: [] };
                        }

                        // Penalty for length difference
                        score -= Math.abs(text.length - query.length) * 0.5;

                        return { score: Math.max(0, score), matches };
                    }

                                        function clearHoverEffects() {
                        const items = results.querySelectorAll(".command-palette-item");
                        items.forEach(item => {
                            item.style.background = "";
                            item.style.color = "";
                        });
                    }

                    function filterItems(query) {
                        if (!query.trim()) {
                            filteredItems = [...items];
                        } else {
                            // Separate tables and databases
                            const tables = items.filter(item => item.type === "table");
                            const databases = items.filter(item => item.type === "database");

                            // Score tables
                            const scoredTables = tables.map(item => ({
                                ...item,
                                fuzzyResult: fuzzyMatch(item.name, query)
                            }))
                            .filter(item => item.fuzzyResult.score > 0)
                            .sort((a, b) => b.fuzzyResult.score - a.fuzzyResult.score);

                            // Score databases
                            const scoredDatabases = databases.map(item => ({
                                ...item,
                                fuzzyResult: fuzzyMatch(item.name, query)
                            }))
                            .filter(item => item.fuzzyResult.score > 0)
                            .sort((a, b) => b.fuzzyResult.score - a.fuzzyResult.score);

                            // Merge: tables first, then databases
                            filteredItems = [...scoredTables, ...scoredDatabases];
                        }

                        selectedIndex = 0;
                        isKeyboardNavigation = false;
                        lastHoveredIndex = -1;
                        console.log("Filter changed, reset lastHoveredIndex");
                        renderResults();
                    }

                    function openPalette() {
                        overlay.style.display = "flex";
                        input.value = "";
                        filteredItems = [...items];
                        selectedIndex = 0;
                        isKeyboardNavigation = false;
                        keyboardActionTime = 0;
                        lastHoveredIndex = -1;
                        renderResults();
                        input.focus();
                    }

                    function closePalette() {
                        overlay.style.display = "none";
                    }

                    function selectItem() {
                        if (filteredItems[selectedIndex]) {
                            window.location.href = filteredItems[selectedIndex].url;
                        }
                    }

                    // Event listeners
                    input.addEventListener("input", (e) => {
                        filterItems(e.target.value);
                    });

                    input.addEventListener("keydown", (e) => {
                        switch(e.key) {
                            case "ArrowDown":
                                e.preventDefault();
                                if (filteredItems.length > 0) {
                                    const wasKeyboardNavigation = isKeyboardNavigation;
                                    if (!isKeyboardNavigation) {
                                        // Clear any hover effects when starting keyboard navigation
                                        clearHoverEffects();
                                        // If we have a hovered item, start from there
                                        if (lastHoveredIndex >= 0 && lastHoveredIndex < filteredItems.length) {
                                            selectedIndex = lastHoveredIndex;
                                            console.log("Starting keyboard navigation from hovered index:", selectedIndex);
                                        }
                                    }
                                    isKeyboardNavigation = true;
                                    keyboardActionTime = Date.now();

                                    // Only increment if we were already in keyboard mode OR we had a hovered item
                                    if (wasKeyboardNavigation || lastHoveredIndex >= 0) {
                                        selectedIndex = Math.min(selectedIndex + 1, filteredItems.length - 1);
                                    }
                                    // If this is the first arrow down and no hover, stay at index 0

                                    renderResults();
                                }
                                break;
                            case "ArrowUp":
                                e.preventDefault();
                                if (filteredItems.length > 0) {
                                    const wasKeyboardNavigation = isKeyboardNavigation;
                                    if (!isKeyboardNavigation) {
                                        // Clear any hover effects when starting keyboard navigation
                                        clearHoverEffects();
                                        // If we have a hovered item, start from there
                                        if (lastHoveredIndex >= 0 && lastHoveredIndex < filteredItems.length) {
                                            selectedIndex = lastHoveredIndex;
                                            console.log("Starting keyboard navigation from hovered index:", selectedIndex);
                                        }
                                    }
                                    isKeyboardNavigation = true;
                                    keyboardActionTime = Date.now();

                                    // Only decrement if we were already in keyboard mode OR we had a hovered item
                                    if (wasKeyboardNavigation || lastHoveredIndex >= 0) {
                                        selectedIndex = Math.max(selectedIndex - 1, 0);
                                    }
                                    // If this is the first arrow up and no hover, stay at index 0

                                    renderResults();
                                }
                                break;
                            case "Enter":
                                e.preventDefault();
                                selectItem();
                                break;
                        }
                    });

                    overlay.addEventListener("click", (e) => {
                        if (e.target === overlay) {
                            closePalette();
                        }
                    });

                    // Track mouse movement to switch back from keyboard mode
                    palette.addEventListener("mousemove", (e) => {
                        if (isKeyboardNavigation) {
                            const currentTime = Date.now();
                            const timeSinceKeyboard = currentTime - keyboardActionTime;

                            // Only check mouse movement if some time has passed since last keyboard action
                            if (timeSinceKeyboard > 10) {
                                                                const deltaX = Math.abs(e.clientX - lastMouseX);
                                const deltaY = Math.abs(e.clientY - lastMouseY);
                                const distance = Math.sqrt(deltaX * deltaX + deltaY * deltaY);

                                // More sensitive detection: smaller distance OR any significant Y movement
                                if (distance > 5 || deltaY > 3) {
                                    isKeyboardNavigation = false;
                                    clearHoverEffects();
                                    // Update selectedIndex to last hovered item when switching to mouse mode
                                    if (lastHoveredIndex >= 0 && lastHoveredIndex < filteredItems.length) {
                                        selectedIndex = lastHoveredIndex;
                                        console.log("Switching to mouse mode, selectedIndex:", selectedIndex, "filteredItems.length:", filteredItems.length);
                                    } else {
                                        console.log("Invalid lastHoveredIndex:", lastHoveredIndex, "filteredItems.length:", filteredItems.length);
                                    }
                                    renderResults();
                                }
                            }
                        }

                        lastMouseX = e.clientX;
                        lastMouseY = e.clientY;
                    });

                                        // Global keyboard shortcuts
                    document.addEventListener("keydown", (e) => {
                        // Cmd+K to toggle palette
                        if ((e.metaKey || e.ctrlKey) && e.key === "k") {
                            e.preventDefault();
                            if (overlay.style.display === "flex") {
                                closePalette();
                            } else {
                                openPalette();
                            }
                        }
                        // Cmd+J to copy filtered items as JSON (debug)
                        else if ((e.metaKey || e.ctrlKey) && e.key === "j" && overlay.style.display === "flex") {
                            e.preventDefault();
                            const debugData = {
                                query: input.value,
                                totalItems: items.length,
                                filteredCount: filteredItems.length,
                                filteredItems: filteredItems.map(item => ({
                                    name: item.name,
                                    type: item.type,
                                    url: item.url,
                                    score: item.fuzzyResult ? item.fuzzyResult.score : "no-score"
                                }))
                            };
                            navigator.clipboard.writeText(JSON.stringify(debugData, null, 2)).then(() => {
                                console.log("Debug data copied to clipboard:", debugData);
                            }).catch(err => {
                                console.error("Failed to copy to clipboard:", err);
                                console.log("Debug data:", debugData);
                            });
                        }
                        // ESC to close palette
                        else if (e.key === "Escape" && overlay.style.display === "flex") {
                            e.preventDefault();
                            closePalette();
                        }
                    }, true);



                    // Initialize
                    renderResults();
                });
            </script>';
        }
    }

    protected $translations = array(
        'en' => array('' => 'Command palette for quick table and database navigation'),
    );
}