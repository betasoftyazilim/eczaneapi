package tr.com.betasoft.eczaneapi.models;

import com.google.gson.annotations.SerializedName;
import java.util.List;

public class IlModel {
    @SerializedName("il")
    private String il;

    @SerializedName("plaka")
    private Integer plaka;

    @SerializedName("ilceler")
    private List<String> ilceler;

    public String getIl() { return il; }
    public Integer getPlaka() { return plaka; }
    public List<String> getIlceler() { return ilceler; }
}
