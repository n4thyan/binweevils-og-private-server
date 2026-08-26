require("./Weevil");
require("./BinWeevils");
const e = require("express");

class GameFinishObjects {

    constructor() {

        this.RACING = 0;
        this.FINISHED = 1;

        this.AWAITING_PING = 0;
        this.GOT_PING = 1;

        this.m_alreadySetMulch = false;
        this.m_raceState = 0;
        this.m_pingState = 0;
        this.m_shouldTimeOut = false;
        this.m_time = 99999999999999999n;
        this.m_alreadyDeterminedPosition = false;

        this.m_user = undefined;

    }

    alreadySetMulch() {
        return this.m_alreadySetMulch;
    }

    setAlreadySetMulch(val) {
        this.m_alreadySetMulch = val;
    }

    setAlreadyDeterminedPosition(val) {
        this.m_alreadyDeterminedPosition = val;
    }

    setShouldTimeOut(state) {
        this.m_shouldTimeOut = state;
    }

    setPingState(state) {
        this.m_pingState = state;
    }

    setUser(weevil) {
        this.m_user = weevil;
    }

    setTime(time) {
        this.m_time = time;
    }

    getAlreadyDeterminedPosition() {
        return this.m_alreadyDeterminedPosition;
    }

    getShouldTimeOut() {
        return this.m_shouldTimeOut;
    }

    getRaceState() {
        return this.m_raceState;
    }

    getpingState() {
        return this.m_pingState;
    }

    getUser() {
        return this.m_user;
    }

    getTime() {
        return this.m_time;
    }

}

module.exports = GameFinishObjects;