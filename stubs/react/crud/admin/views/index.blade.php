import React from "react";

import {{ $modelName }}Repository from "../../repositories/{{ $modelName }}Repository";
import columns from './_columns'
import info from './_info'
import {withRouter} from 'react-router-dom'
import Index from "../CRUDBase/Index";

class {{ $modelName }}Index extends Index {

  setPageInfo() {
    this.title = info.title;
    this.path = info.path;
  }

  setRepository() {
    this.repository = new {{ $modelName }}Repository();
  }

  setColumnInfo() {
    this.columns = columns;
  }

}

export default withRouter({{ $modelName }}Index);
