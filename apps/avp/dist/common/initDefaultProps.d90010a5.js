import { _ as n } from '../main.4e11cfe3.js';
const p = (s, o) => {
    const r = n({}, s);
    return (
      Object.keys(o).forEach(t => {
        const e = r[t];
        if (e)
          e.type || e.default
            ? (e.default = o[t])
            : e.def
            ? e.def(o[t])
            : (r[t] = { type: e, default: o[t] });
        else throw new Error(`not have ${t} prop`);
      }),
      r
    );
  },
  i = p;
export { i };
