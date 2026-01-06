import { ethers } from "https://cdnjs.cloudflare.com/ajax/libs/ethers/6.7.0/ethers.min.js";

const BASE_CHAIN_ID = "0x2105";
const TOKEN_CONTRACT_ADDRESS = "0x833589fcd6edb6e08f4c7c32d4f71b54bda02913"; // USDC on Base Mainnet
const ERC20_ABI = ["function transfer(address to, uint amount) returns (bool)"];

window.TapSNDRMenu = (function () {
    const partId = "tapsndr-menu";

    const selectors = {
        balance: {
            value: "." + partId + "-balance > div > div > div",
            buttons: {
                deposit: "." + partId + "-btn-deposit",
            },
        },
        items: {
            tickets: "." + partId + "-item-tickets",
            transactions: "." + partId + "-item-transactions",
        },
    };

    const prevStates = {
        wallet: null,
    };
    const states = { ...prevStates };

    const getWallet = (cb) => {
        TapSNDRUtils.ajax("get", serverUrl + "/web/users/" + TapSNDRCurrentUser.id + "/wallet", (success, data, error) => {
            if (!success) {
                TapSNDRUtils.toast("error", error);
                return;
            }
            cb(data);
        });
    };

    const onStatesChanged = () => {
        if (states.wallet !== prevStates.wallet) {
            $(selectors.balance.value).html(accounting.formatMoney(states.wallet.balance));
        }

        prevStates.wallet = states.wallet;
    };

    const onDeposit = async function () {
        if (!window.ethereum?.isMetaMask) {
            TapSNDRUtils.toast("error", "Please install MetaMask to deposit funds.");
            return;
        }

        try {
            await window.ethereum.request({ method: "eth_requestAccounts" });

            const chainId = await window.ethereum.request({ method: "eth_chainId" });
            if (chainId !== BASE_CHAIN_ID) {
                await window.ethereum.request({
                    method: "wallet_switchEthereumChain",
                    params: [{ chainId: BASE_CHAIN_ID }],
                });
            }

            const provider = new ethers.BrowserProvider(window.ethereum);
            const signer = await provider.getSigner();

            const contract = new ethers.Contract(TOKEN_CONTRACT_ADDRESS, ERC20_ABI, signer);

            const amount = ethers.parseUnits(String(100), 6);

            const tx = await contract.transfer("0x789531f07Db64104b2De027b0DD422DF2fd59EB1", amount);

            const receipt = await tx.wait();
        } catch (error) {
            console.log(error);
        }
    };

    const setEvents = () => {
        $(selectors.balance.buttons.deposit).on("click", onDeposit);
    };

    const setActive = (menuItem) => {
        $("." + partId + "-" + menuItem).addClass("show");
    };

    return {
        init: () => {
            getWallet((wallet) => {
                states.wallet = wallet;
                onStatesChanged();
            });
            setEvents();
        },
        setActive,
    };
})();
