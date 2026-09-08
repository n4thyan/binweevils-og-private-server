'use strict';

const fs = require('fs');
const path = require('path');

const DEFAULT_REGISTRY_PATH = path.join(__dirname, 'config', 'teleporter-destinations.json');

function loadRegistry(registryPath = DEFAULT_REGISTRY_PATH) {
    const registry = JSON.parse(fs.readFileSync(registryPath, 'utf8'));
    validateRegistry(registry);
    return registry;
}

function validateRegistry(registry) {
    if (!registry || !registry.categoryWeights || !Array.isArray(registry.destinations)) {
        throw new Error('Invalid teleporter registry structure');
    }

    const categories = Object.keys(registry.categoryWeights);
    const categoryTotal = categories.reduce((sum, category) => sum + Number(registry.categoryWeights[category]), 0);
    if (Math.abs(categoryTotal - 100) > 0.0001) {
        throw new Error('Teleporter category weights must total 100');
    }

    const locIds = new Set();
    for (const destination of registry.destinations) {
        if (!Number.isInteger(destination.locId) || locIds.has(destination.locId)) {
            throw new Error('Teleporter locId must be a unique integer: ' + destination.locId);
        }
        locIds.add(destination.locId);
        if (!categories.includes(destination.category)) {
            throw new Error('Unknown teleporter category for locId ' + destination.locId);
        }
        if (destination.enabled && (!(Number(destination.weight) > 0) || !Number.isInteger(destination.roomId))) {
            throw new Error('Enabled teleporter destination is incomplete: ' + destination.locId);
        }
    }

    return true;
}

function weightedPick(entries, getWeight, rng) {
    const total = entries.reduce((sum, entry) => sum + Number(getWeight(entry)), 0);
    if (!(total > 0)) return null;

    let roll = rng() * total;
    for (const entry of entries) {
        roll -= Number(getWeight(entry));
        if (roll < 0) return entry;
    }
    return entries[entries.length - 1];
}

function selectDestination(registry, state = {}, rng = Math.random) {
    validateRegistry(registry);

    const currentLocId = Number.isInteger(state.currentLocId) ? state.currentLocId : null;
    const historySize = Number.isInteger(registry.recentHistorySize) ? registry.recentHistorySize : 5;
    const recent = Array.isArray(state.recentLocIds)
        ? state.recentLocIds.filter(Number.isInteger).slice(0, historySize)
        : [];
    const immediatePrevious = recent.length ? recent[0] : null;

    const enabled = registry.destinations.filter(destination => destination.enabled && destination.locId !== currentLocId);
    if (!enabled.length) throw new Error('No eligible teleporter destinations');

    // Pick the probability bucket first. Adding destinations never changes its configured probability.
    const availableCategories = Object.keys(registry.categoryWeights).filter(category =>
        enabled.some(destination => destination.category === category && destination.locId !== immediatePrevious)
    );
    const category = weightedPick(
        availableCategories,
        name => registry.categoryWeights[name],
        rng
    );

    let candidates = enabled.filter(destination =>
        destination.category === category && !recent.includes(destination.locId)
    );

    // If a tiny category is exhausted, retain immediate-repeat protection before relaxing older history.
    if (!candidates.length) {
        candidates = enabled.filter(destination =>
            destination.category === category && destination.locId !== immediatePrevious
        );
    }
    if (!candidates.length) {
        candidates = enabled.filter(destination => destination.category === category);
    }

    const destination = weightedPick(candidates, entry => entry.weight, rng);
    if (!destination) throw new Error('Selected category has no eligible destinations: ' + category);
    return destination;
}

module.exports = {
    DEFAULT_REGISTRY_PATH,
    loadRegistry,
    selectDestination,
    validateRegistry,
    weightedPick
};
